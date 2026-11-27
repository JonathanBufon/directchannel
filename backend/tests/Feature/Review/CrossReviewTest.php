<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

use App\Models\AccessGrant;
use App\Models\AccessGrantToken;
use App\Models\AccountBlock;
use App\Models\Attachment;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Message;
use App\Models\MessageReceipt;
use App\Models\PersonalAccessToken;
use App\Models\PublicKeyMaterial;
use App\Models\Report;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Users\ApiTestCase;

final class CrossReviewTest extends ApiTestCase
{
    private function participant(Conversation $conversation, User $user, string $joinedAt): ConversationParticipant
    {
        $participant = ConversationParticipant::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $user->id]);
        DB::table('conversation_participants')->where('id', $participant->id)->update(['joined_at' => $joinedAt]);

        return $participant->refresh();
    }

    public static function attachmentOperations(): array
    {
        return [
            ['GET', '', true], ['GET', '/download', true], ['PATCH', '', true], ['DELETE', '', true],
            ['GET', '', false], ['GET', '/download', false], ['PATCH', '', false], ['DELETE', '', false],
        ];
    }

    #[DataProvider('attachmentOperations')]
    public function test_attachment_before_active_join_is_hidden_even_from_its_author(string $method, string $suffix, bool $isAuthor): void
    {
        Storage::fake('local');
        $user = $this->user();
        $this->signIn($user);
        $conversation = Conversation::factory()->create();
        $former = $this->participant($conversation, $user, '2026-01-01 12:00:00.100000+00');
        $sender = $isAuthor ? $former : $this->participant($conversation, $this->user(), '2026-01-01 12:00:00+00');
        $message = Message::factory()->create(['conversation_id' => $conversation->id, 'sender_participant_id' => $sender->id]);
        DB::table('messages')->where('id', $message->id)->update(['accepted_at' => '2026-01-01 12:00:00.400000+00']);
        $former->update(['left_at' => now()]);
        $active = $this->participant($conversation, $user, '2026-01-01 12:00:00.800000+00');
        $attachment = Attachment::factory()->create(['message_id' => $message->id]);
        Storage::disk('local')->put($attachment->storage_key, 'ciphertext');
        $uri = '/api/attachments/'.$attachment->id.$suffix;
        $this->api($method, $uri)->assertNotFound();
        // Authorship must not affect the 404; the exact join boundary is inclusive.
        DB::table('messages')->where('id', $message->id)->update(['accepted_at' => $active->getRawOriginal('joined_at')]);
        $this->api($method, $uri)->assertStatus(in_array($method, ['PATCH', 'DELETE'], true) ? ($isAuthor ? 501 : 403) : 200);
    }

    public static function domainTimestamps(): array
    {
        return [
            [AccessGrant::class, 'started_at'],
            [AccountBlock::class, 'blocked_at'],
            [Conversation::class, 'created_at'],
            [ConversationParticipant::class, 'joined_at'],
            [Message::class, 'accepted_at'],
            [MessageReceipt::class, 'delivered_at'],
            [PublicKeyMaterial::class, 'published_at'],
            [Report::class, 'created_at'],
            [TemporaryUpload::class, 'created_at'],
        ];
    }

    #[DataProvider('domainTimestamps')]
    public function test_domain_event_timestamp_preserves_microseconds_after_database_round_trip(string $modelClass, string $column): void
    {
        $instant = now()->setMicrosecond(123456);
        $model = $modelClass::factory()->create([$column => $instant]);
        $this->assertSame($instant->format('Y-m-d H:i:s.uP'), $model->refresh()->{$column}->format('Y-m-d H:i:s.uP'));
    }

    public function test_direct_reentry_preserves_microseconds_and_hides_message_sent_during_absence(): void
    {
        $me = $this->user();
        $other = $this->user();
        $this->travelTo(now()->addSecond()->setMicrosecond(100000));
        $this->signIn($me);
        $id = $this->api('POST', '/api/conversations', ['user_id' => $other->id])->assertCreated()->json('data.id');
        $this->travelTo(now()->setMicrosecond(200000));
        $this->api('DELETE', '/api/conversations/'.$id)->assertOk();
        $this->signIn($other);
        $this->travelTo(now()->setMicrosecond(400000));
        $hidden = $this->api('POST', "/api/conversations/$id/messages", ['encrypted_body' => 'YQ=='])->assertCreated()->json('data.id');
        $this->signIn($me);
        $this->travelTo(now()->setMicrosecond(800000));
        $this->api('POST', '/api/conversations', ['user_id' => $other->id])->assertOk();
        $participant = Conversation::findOrFail($id)->activeParticipantFor($me);
        $this->assertSame('800000', $participant->joined_at->format('u'));
        $this->api('GET', "/api/conversations/$id/messages")->assertOk()->assertJsonCount(0, 'data');
        $this->api('GET', "/api/conversations/$id/messages?before_id=$hidden")->assertNotFound();
        $this->api('POST', "/api/messages/$hidden/read")->assertNotFound();
        $visible = $this->api('POST', "/api/conversations/$id/messages", ['encrypted_body' => 'Yg=='])->assertCreated()->json('data.id');
        $this->api('GET', "/api/conversations/$id/messages")->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $visible);
    }

    public function test_preview_and_list_order_use_only_messages_visible_to_requester(): void
    {
        $user = $this->user();
        $this->signIn($user);
        $hiddenChat = Conversation::factory()->create(['created_at' => '2026-01-01 10:00:00']);
        $sender = $this->participant($hiddenChat, $this->user(), '2026-01-01 10:00:00+00');
        $this->participant($hiddenChat, $user, '2026-01-01 12:00:00.800000+00');
        $hidden = Message::factory()->create(['conversation_id' => $hiddenChat->id, 'sender_participant_id' => $sender->id]);
        DB::table('messages')->where('id', $hidden->id)->update(['accepted_at' => '2026-01-01 12:00:00.400000+00']);
        $visibleChat = Conversation::factory()->create(['created_at' => '2026-01-01 11:00:00']);
        $visibleSender = $this->participant($visibleChat, $user, '2026-01-01 11:00:00+00');
        $visible = Message::factory()->create(['conversation_id' => $visibleChat->id, 'sender_participant_id' => $visibleSender->id, 'accepted_at' => '2026-01-01 12:00:00+00']);
        $this->api('GET', '/api/conversations/'.$hiddenChat->id)->assertOk()->assertJsonPath('data.latest_message', null);
        $this->api('GET', '/api/conversations')->assertOk()->assertJsonPath('data.0.id', $visibleChat->id)
            ->assertJsonPath('data.0.latest_message.id', $visible->id)->assertJsonPath('data.1.latest_message', null);
        $this->api('POST', '/api/conversations', ['user_id' => $sender->user_id])->assertOk()->assertJsonPath('data.latest_message', null);
    }

    public function test_listing_more_conversations_does_not_add_queries_per_conversation(): void
    {
        $user = $this->user();
        $this->signIn($user);
        $createChat = function () use ($user): void {
            $participant = ConversationParticipant::factory()->create(['user_id' => $user->id, 'joined_at' => now()->subDay()]);
            Message::factory()->create(['conversation_id' => $participant->conversation_id, 'sender_participant_id' => $participant->id]);
        };
        $createChat();
        DB::enableQueryLog();
        try {
            $this->api('GET', '/api/conversations')->assertOk()->assertJsonCount(1, 'data');
            $selectCount = fn (): int => count(array_filter(DB::getQueryLog(), fn (array $query): bool => str_starts_with($query['query'], 'select')));
            $single = $selectCount();
            for ($i = 0; $i < 5; $i++) {
                $createChat();
            }
            DB::flushQueryLog();
            $this->api('GET', '/api/conversations')->assertOk()->assertJsonCount(6, 'data');
            $this->assertSame($single, $selectCount());
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_upload_is_bound_to_token_grant_and_cannot_cross_reauthentication(): void
    {
        Storage::fake('local');
        $user = $this->user();
        $token = $this->signIn($user);
        $conversation = ConversationParticipant::factory()->create(['user_id' => $user->id, 'joined_at' => now()->subDay()])->conversation;
        $uploadId = $this->api('POST', "/api/conversations/$conversation->id/uploads", ['file' => UploadedFile::fake()->create('a.bin', 1)])->assertCreated()->json('data.id');
        $grantId = AccessGrantToken::findOrFail(PersonalAccessToken::findToken($token)->id)->access_grant_id;
        $this->assertSame($grantId, TemporaryUpload::findOrFail($uploadId)->access_grant_id);
        $this->api('POST', '/api/logout')->assertOk();
        $this->signIn($user);
        $this->api('POST', "/api/conversations/$conversation->id/messages", ['encrypted_body' => 'YQ==', 'temporary_upload_ids' => [$uploadId]])
            ->assertUnprocessable()->assertJsonValidationErrors('temporary_upload_ids');
        $this->assertNull(TemporaryUpload::findOrFail($uploadId)->consumed_at);
        $this->assertDatabaseCount('messages', 0);
        $this->assertDatabaseCount('attachments', 0);
    }

    public function test_upload_from_another_grant_is_rejected_atomically(): void
    {
        Storage::fake('local');
        $user = $this->user();
        $this->signIn($user);
        $conversation = ConversationParticipant::factory()->create(['user_id' => $user->id, 'joined_at' => now()->subDay()])->conversation;
        $uploadId = $this->api('POST', "/api/conversations/$conversation->id/uploads", ['file' => UploadedFile::fake()->create('a.bin', 1)])->assertCreated()->json('data.id');
        // A new authenticated context for the same account must not inherit this upload.
        $this->signIn($user);
        $this->api('POST', "/api/conversations/$conversation->id/messages", ['encrypted_body' => 'YQ==', 'temporary_upload_ids' => [$uploadId]])
            ->assertUnprocessable()->assertJsonValidationErrors('temporary_upload_ids');
        $this->assertNull(TemporaryUpload::findOrFail($uploadId)->consumed_at);
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_deactivation_requires_transfer_if_sole_responsible_with_other_active_members(): void
    {
        $user = $this->user();
        $token = $this->signIn($user);
        $group = Group::factory()->create();
        $owner = ConversationParticipant::factory()->create(['user_id' => $user->id, 'conversation_id' => $group->conversation_id]);
        GroupMember::create(['participant_id' => $owner->id, 'group_id' => $group->id, 'is_responsible' => true]);
        $member = ConversationParticipant::factory()->create(['conversation_id' => $group->conversation_id]);
        GroupMember::create(['participant_id' => $member->id, 'group_id' => $group->id, 'is_responsible' => false]);
        $formerOwner = ConversationParticipant::factory()->create(['conversation_id' => $group->conversation_id]);
        GroupMember::create(['participant_id' => $formerOwner->id, 'group_id' => $group->id, 'is_responsible' => true]);
        $formerOwner->user->delete();
        $this->api('DELETE', '/api/me', ['password' => 'senha-segura'])->assertUnprocessable()->assertJsonValidationErrors('groups');
        $this->assertFalse($user->refresh()->trashed());
        $this->assertNotNull(PersonalAccessToken::findToken($token));
        $this->assertTrue($user->accessGrants()->active()->exists());
        $this->api('POST', "/api/groups/$group->id/responsibles/$member->user_id")->assertNoContent();
        $this->api('DELETE', '/api/me', ['password' => 'senha-segura'])->assertOk();
        $this->assertSoftDeleted($user);
    }

    public function test_deactivation_is_allowed_when_no_other_active_group_members_remain(): void
    {
        $user = $this->user();
        $this->signIn($user);
        $group = Group::factory()->create();
        $owner = ConversationParticipant::factory()->create(['user_id' => $user->id, 'conversation_id' => $group->conversation_id]);
        GroupMember::create(['participant_id' => $owner->id, 'group_id' => $group->id, 'is_responsible' => true]);
        $former = ConversationParticipant::factory()->left()->create(['conversation_id' => $group->conversation_id]);
        GroupMember::create(['participant_id' => $former->id, 'group_id' => $group->id, 'is_responsible' => false]);
        $this->api('DELETE', '/api/me', ['password' => 'senha-segura'])->assertOk();
        $this->assertSoftDeleted($user);
    }
}
