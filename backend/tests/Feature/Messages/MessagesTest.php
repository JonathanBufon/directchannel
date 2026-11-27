<?php

declare(strict_types=1);

namespace Tests\Feature\Messages;

use App\Models\AccessGrant;
use App\Models\AccessGrantToken;
use App\Models\Attachment;
use App\Models\Block;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageReceipt;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class MessagesTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(User $user): void
    {
        $this->app['auth']->forgetGuards();
        $grant = AccessGrant::firstOrCreate(['user_id' => $user->id, 'revoked_at' => null], ['started_at' => now()]);
        $token = $user->createToken('test');
        AccessGrantToken::create(['personal_access_token_id' => $token->accessToken->id, 'access_grant_id' => $grant->id]);
        $this->withToken($token->plainTextToken);
    }

    private function chat(bool $group = false): array
    {
        $conversation = $group ? Conversation::factory()->group()->create() : Conversation::factory()->create();
        $sender = ConversationParticipant::factory()->create(['conversation_id' => $conversation->id, 'joined_at' => now()->subDay()]);
        $reader = ConversationParticipant::factory()->create(['conversation_id' => $conversation->id, 'joined_at' => now()->subDay()]);

        return [$conversation, $sender, $reader];
    }

    private function message(ConversationParticipant $sender, array $attributes = []): Message
    {
        return Message::factory()->create($attributes + ['conversation_id' => $sender->conversation_id, 'sender_participant_id' => $sender->id]);
    }

    public function test_send_stores_binary_attachments_and_only_active_recipient_receipts(): void
    {
        [$conversation, $sender, $reader] = $this->chat(true);
        $former = ConversationParticipant::factory()->left()->create(['conversation_id' => $conversation->id]);
        $upload = TemporaryUpload::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $sender->user_id]);
        $this->signIn($sender->user);
        $body = base64_encode("\x00\xff\x80ciphertext");
        $response = $this->postJson("/api/conversations/$conversation->id/messages", ['encrypted_body' => $body, 'temporary_upload_ids' => [$upload->id]])
            ->assertCreated()->assertJsonPath('data.encrypted_body', $body)->assertJsonPath('data.sender.id', $sender->user_id)
            ->assertJsonPath('data.receipt', null)->assertJsonPath('data.attachments.0.size_bytes', $upload->size_bytes);
        $message = Message::findOrFail($response->json('data.id'));
        $this->assertSame(base64_decode($body), $message->encrypted_body);
        $this->assertDatabaseHas('attachments', ['message_id' => $message->id, 'storage_key' => $upload->storage_key]);
        $this->assertNotNull($upload->refresh()->consumed_at);
        $this->assertDatabaseHas('message_receipts', ['message_id' => $message->id, 'recipient_participant_id' => $reader->id, 'read_at' => null]);
        $this->assertDatabaseMissing('message_receipts', ['message_id' => $message->id, 'recipient_participant_id' => $sender->id]);
        $this->assertDatabaseMissing('message_receipts', ['message_id' => $message->id, 'recipient_participant_id' => $former->id]);
        $this->assertArrayNotHasKey('storage_key', $response->json('data.attachments.0'));
    }

    public function test_history_orders_by_time_then_id_and_returns_reader_receipt_without_private_fields(): void
    {
        [$conversation, $sender, $reader] = $this->chat();
        $newer = $this->message($sender, ['accepted_at' => now()]);
        $older = $this->message($sender, ['accepted_at' => now()->subHour()]);
        $tied = $this->message($sender, ['accepted_at' => $older->accepted_at]);
        $attachment = Attachment::factory()->create(['message_id' => $newer->id]);
        MessageReceipt::create(['message_id' => $newer->id, 'recipient_participant_id' => $reader->id, 'delivered_at' => now()]);
        $this->signIn($reader->user);
        $response = $this->getJson("/api/conversations/$conversation->id/messages?limit=2")
            ->assertOk()->assertJsonPath('data.0.id', $newer->id)->assertJsonPath('data.1.id', $tied->id)
            ->assertJsonPath('meta.next_before_id', $tied->id)->assertJsonPath('data.0.attachments.0.id', $attachment->id);
        $this->assertNotNull($response->json('data.0.receipt.delivered_at'));
        $this->assertSame(base64_encode($newer->encrypted_body), $response->json('data.0.encrypted_body'));
        $this->assertArrayNotHasKey('email', $response->json('data.0.sender'));
        $this->getJson("/api/conversations/$conversation->id/messages?before_id=$tied->id")
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $older->id);
    }

    public function test_history_preserves_microseconds_for_cursor_and_join_boundary(): void
    {
        [$conversation, $sender, $reader] = $this->chat();
        DB::table('conversation_participants')->where('id', $reader->id)->update(['joined_at' => '2026-01-01 12:00:00.500000+00']);
        $hidden = $this->message($sender);
        $visible = $this->message($sender);
        $cursor = $this->message($sender);
        foreach ([[$hidden, '499999'], [$visible, '500000'], [$cursor, '900000']] as [$message, $fraction]) {
            DB::table('messages')->where('id', $message->id)->update(['accepted_at' => "2026-01-01 12:00:00.$fraction+00"]);
        }
        $this->signIn($reader->user);
        $this->getJson("/api/conversations/$conversation->id/messages?before_id=$cursor->id")
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $visible->id);
        $this->getJson("/api/conversations/$conversation->id/messages?before_id=$hidden->id")->assertNotFound();
        $this->postJson("/api/messages/$hidden->id/read")->assertNotFound();
    }

    public function test_history_has_default_and_maximum_limits_and_rejects_foreign_cursor(): void
    {
        [$conversation, $sender, $reader] = $this->chat();
        Message::factory()->count(105)->create(['conversation_id' => $conversation->id, 'sender_participant_id' => $sender->id]);
        $foreign = Message::factory()->create();
        $this->signIn($reader->user);
        $this->getJson("/api/conversations/$conversation->id/messages")->assertOk()->assertJsonCount(30, 'data');
        $this->getJson("/api/conversations/$conversation->id/messages?limit=100")->assertOk()->assertJsonCount(100, 'data');
        foreach (['limit=101', 'limit=0', 'limit=no', 'before_id=0', 'before_id=no'] as $query) {
            $this->getJson("/api/conversations/$conversation->id/messages?$query")->assertUnprocessable();
        }
        $this->getJson("/api/conversations/$conversation->id/messages?before_id=$foreign->id")->assertNotFound();
    }

    public static function invalidBodies(): array
    {
        return [
            'missing' => [[]], 'empty' => [['encrypted_body' => '']],
            'not base64' => [['encrypted_body' => '???']], 'wrong type' => [['encrypted_body' => 12]],
            'whitespace' => [['encrypted_body' => 'Y Q==']],
            'too large' => [['encrypted_body' => base64_encode(str_repeat('a', 65537))]],
            'duplicate upload' => [['encrypted_body' => 'YQ==', 'temporary_upload_ids' => [1, 1]]],
            'too many uploads' => [['encrypted_body' => 'YQ==', 'temporary_upload_ids' => range(1, 11)]],
            'upload type' => [['encrypted_body' => 'YQ==', 'temporary_upload_ids' => 'bad']],
            'upload id' => [['encrypted_body' => 'YQ==', 'temporary_upload_ids' => ['bad']]],
        ];
    }

    #[DataProvider('invalidBodies')]
    public function test_send_validation(array $payload): void
    {
        [$conversation, $sender] = $this->chat();
        $this->signIn($sender->user);
        $this->postJson("/api/conversations/$conversation->id/messages", $payload)->assertUnprocessable();
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_send_accepts_exactly_64_kib(): void
    {
        [$conversation, $sender] = $this->chat();
        $this->signIn($sender->user);
        $body = base64_encode(str_repeat("\xff", 65536));
        $this->postJson("/api/conversations/$conversation->id/messages", ['encrypted_body' => $body])
            ->assertCreated()->assertJsonPath('data.encrypted_body', $body);
    }

    public static function invalidUploads(): array
    {
        return array_map(fn ($kind): array => [$kind], ['other author', 'other conversation', 'expired', 'consumed', 'missing']);
    }

    #[DataProvider('invalidUploads')]
    public function test_invalid_upload_rolls_back_the_whole_send(string $kind): void
    {
        [$conversation, $sender] = $this->chat();
        $good = TemporaryUpload::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $sender->user_id]);
        $bad = TemporaryUpload::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $sender->user_id]);
        match ($kind) {
            'other author' => $bad->update(['user_id' => User::factory()->create()->id]),
            'other conversation' => $bad->update(['conversation_id' => Conversation::factory()->create()->id]),
            'expired' => $bad->update(['expires_at' => now()]),
            'consumed' => $bad->update(['consumed_at' => now()]),
            'missing' => $bad->delete(),
        };
        $this->signIn($sender->user);
        $this->postJson("/api/conversations/$conversation->id/messages", ['encrypted_body' => 'YQ==', 'temporary_upload_ids' => [$good->id, $bad->id]])
            ->assertUnprocessable()->assertJsonValidationErrors('temporary_upload_ids');
        $this->assertNull($good->refresh()->consumed_at);
        $this->assertDatabaseCount('messages', 0);
        $this->assertDatabaseCount('attachments', 0);
        $this->assertDatabaseCount('message_receipts', 0);
    }

    public function test_upload_cannot_be_consumed_twice(): void
    {
        [$conversation, $sender] = $this->chat();
        $upload = TemporaryUpload::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $sender->user_id]);
        $this->signIn($sender->user);
        $payload = ['encrypted_body' => 'YQ==', 'temporary_upload_ids' => [$upload->id]];
        $this->postJson("/api/conversations/$conversation->id/messages", $payload)->assertCreated();
        $this->postJson("/api/conversations/$conversation->id/messages", $payload)->assertUnprocessable();
        $this->assertDatabaseCount('messages', 1);
        $this->assertDatabaseCount('attachments', 1);
    }

    public function test_direct_blocks_in_either_direction_prevent_send_but_group_blocks_do_not(): void
    {
        [$conversation, $sender, $reader] = $this->chat();
        $this->signIn($sender->user);
        foreach ([[$sender->user_id, $reader->user_id], [$reader->user_id, $sender->user_id]] as [$blocker, $blocked]) {
            Block::create(['blocker_user_id' => $blocker, 'blocked_user_id' => $blocked]);
            $this->postJson("/api/conversations/$conversation->id/messages", ['encrypted_body' => 'YQ=='])->assertForbidden();
            DB::table('blocks')->delete();
        }
        [$group, $groupSender, $groupReader] = $this->chat(true);
        Block::create(['blocker_user_id' => $groupReader->user_id, 'blocked_user_id' => $groupSender->user_id]);
        $this->signIn($groupSender->user);
        $this->postJson("/api/conversations/$group->id/messages", ['encrypted_body' => 'YQ=='])->assertCreated();
    }

    public function test_receipt_first_marks_win_and_read_implies_delivery(): void
    {
        [$conversation, $sender, $reader] = $this->chat();
        $message = $this->message($sender);
        MessageReceipt::create(['message_id' => $message->id, 'recipient_participant_id' => $reader->id]);
        $this->signIn($reader->user);
        $this->travelTo(now()->startOfSecond());
        $this->postJson("/api/messages/$message->id/read")->assertNoContent();
        $first = DB::table('message_receipts')->first();
        $this->assertNotNull($first->read_at);
        $this->assertSame($first->delivered_at, $first->read_at);
        $this->travel(5)->minutes();
        $this->postJson("/api/messages/$message->id/delivered")->assertNoContent();
        $this->postJson("/api/messages/$message->id/read")->assertNoContent();
        $this->assertEquals($first, DB::table('message_receipts')->first());
    }

    public function test_delivery_then_read_keeps_original_delivery_and_reader_preference_controls_read(): void
    {
        [, $sender, $reader] = $this->chat();
        $reader->user->update(['read_receipts_enabled' => false]);
        $message = $this->message($sender);
        MessageReceipt::create(['message_id' => $message->id, 'recipient_participant_id' => $reader->id]);
        $this->signIn($reader->user->refresh());
        $this->postJson("/api/messages/$message->id/delivered")->assertNoContent();
        $firstDelivery = DB::table('message_receipts')->value('delivered_at');
        $this->travel(1)->minute();
        $this->postJson("/api/messages/$message->id/read")->assertNoContent();
        $this->assertNull(DB::table('message_receipts')->value('read_at'));
        $sender->user->update(['read_receipts_enabled' => false]);
        $reader->user->update(['read_receipts_enabled' => true]);
        $this->signIn($reader->user->refresh());
        $this->postJson("/api/messages/$message->id/read")->assertNoContent();
        $this->assertNotNull(DB::table('message_receipts')->value('read_at'));
        $this->assertSame($firstDelivery, DB::table('message_receipts')->value('delivered_at'));
    }

    public function test_sender_and_active_member_without_recipient_receipt_cannot_mark(): void
    {
        [, $sender, $reader] = $this->chat();
        $message = $this->message($sender);
        foreach ([$sender->user, $reader->user] as $user) {
            $this->signIn($user);
            $this->postJson("/api/messages/$message->id/read")->assertNotFound();
            $this->postJson("/api/messages/$message->id/delivered")->assertNotFound();
        }
    }

    public function test_outsiders_and_former_participants_cannot_access_any_message_endpoint(): void
    {
        [$conversation, $sender, $reader] = $this->chat();
        $message = $this->message($sender);
        $reader->update(['left_at' => now()]);
        foreach ([User::factory()->create(), $reader->user] as $user) {
            $this->signIn($user);
            $this->getJson("/api/conversations/$conversation->id/messages")->assertNotFound();
            $this->postJson("/api/conversations/$conversation->id/messages", ['encrypted_body' => 'YQ=='])->assertNotFound();
            $this->postJson("/api/messages/$message->id/read")->assertNotFound();
            $this->postJson("/api/messages/$message->id/delivered")->assertNotFound();
            $this->patchJson("/api/messages/$message->id")->assertNotFound();
            $this->deleteJson("/api/messages/$message->id")->assertNotFound();
        }
    }

    public function test_future_mutations_return_501_and_preserve_message(): void
    {
        [, $sender] = $this->chat();
        $message = $this->message($sender);
        $this->signIn($sender->user);
        foreach (['PATCH', 'DELETE'] as $method) {
            $this->json($method, "/api/messages/$message->id", ['encrypted_body' => 'YQ=='])
                ->assertStatus(501)->assertJsonPath('message', 'A edição e a exclusão de mensagens estarão disponíveis em uma versão futura.');
        }
        $this->assertSame($message->encrypted_body, $message->refresh()->encrypted_body);
    }

    public function test_non_numeric_message_route_parameters_return_404(): void
    {
        $this->signIn(User::factory()->create());
        $this->getJson('/api/conversations/invalid/messages')->assertNotFound();
        $this->postJson('/api/conversations/invalid/messages')->assertNotFound();
        $this->postJson('/api/messages/invalid/read')->assertNotFound();
        $this->postJson('/api/messages/invalid/delivered')->assertNotFound();
        $this->patchJson('/api/messages/invalid')->assertNotFound();
        $this->deleteJson('/api/messages/invalid')->assertNotFound();
    }

    public function test_read_with_disabled_receipts_still_marks_delivery(): void
    {
        [, $sender, $reader] = $this->chat();
        $reader->user->update(['read_receipts_enabled' => false]);
        $message = $this->message($sender);
        MessageReceipt::create(['message_id' => $message->id, 'recipient_participant_id' => $reader->id]);
        $this->signIn($reader->user->refresh());
        $this->postJson("/api/messages/$message->id/read")->assertNoContent();
        $receipt = DB::table('message_receipts')->first();
        $this->assertNotNull($receipt->delivered_at);
        $this->assertNull($receipt->read_at);
    }

    public function test_all_message_routes_require_authentication(): void
    {
        [$conversation, $sender] = $this->chat();
        $message = $this->message($sender);
        foreach ([['GET', "/api/conversations/$conversation->id/messages"], ['POST', "/api/conversations/$conversation->id/messages"], ['POST', "/api/messages/$message->id/read"], ['POST', "/api/messages/$message->id/delivered"], ['PATCH', "/api/messages/$message->id"], ['DELETE', "/api/messages/$message->id"]] as [$method, $uri]) {
            $this->json($method, $uri)->assertUnauthorized();
        }
    }
}
