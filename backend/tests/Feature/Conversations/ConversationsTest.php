<?php

declare(strict_types=1);

namespace Tests\Feature\Conversations;

use App\Models\Block;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\Feature\Users\ApiTestCase;

final class ConversationsTest extends ApiTestCase
{
    private function participation(Conversation $conversation, User $user, bool $left = false): ConversationParticipant
    {
        return ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id, 'user_id' => $user->id,
            'joined_at' => now()->subDay(), 'left_at' => $left ? now()->subHour() : null,
        ]);
    }

    public function test_create_direct_conversation_and_reuse_same_pair_from_either_direction(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $target = $this->user();
        $created = $this->api('POST', '/api/conversations', ['user_id' => $target->id])->assertCreated()
            ->assertJsonPath('data.kind', 'direct')->assertJsonPath('data.participants.0.user.id', $target->id)->assertJsonPath('data.latest_message', null);
        $id = $created->json('data.id');
        $this->api('POST', '/api/conversations', ['user_id' => $target->id])->assertOk()->assertJsonPath('data.id', $id);
        $this->signIn($target);
        $this->api('POST', '/api/conversations', ['user_id' => $me->id])->assertOk()->assertJsonPath('data.id', $id)->assertJsonPath('data.participants.0.user.id', $me->id);
        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('conversation_participants', 2);
    }

    public function test_create_rejects_self_missing_invalid_unknown_and_deleted_target(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $deleted = $this->user();
        $deleted->delete();
        foreach ([$me->id, null, 'abc', 999999, $deleted->id] as $id) {
            $this->api('POST', '/api/conversations', ['user_id' => $id])->assertUnprocessable()->assertJsonValidationErrors('user_id');
        }
        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_block_in_either_direction_prevents_creation_and_reuse_without_changing_participations(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $target = $this->user();
        Block::factory()->create(['blocker_user_id' => $me->id, 'blocked_user_id' => $target->id]);
        $this->api('POST', '/api/conversations', ['user_id' => $target->id])->assertForbidden();
        $this->assertDatabaseCount('conversations', 0);
        Block::query()->delete();
        $id = $this->api('POST', '/api/conversations', ['user_id' => $target->id])->assertCreated()->json('data.id');
        $this->api('DELETE', '/api/conversations/'.$id)->assertOk();
        Block::factory()->create(['blocker_user_id' => $target->id, 'blocked_user_id' => $me->id]);
        $this->api('POST', '/api/conversations', ['user_id' => $target->id])->assertForbidden();
        $this->assertNull(Conversation::findOrFail($id)->activeParticipantFor($me));
        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_list_includes_active_directs_and_groups_in_activity_order_without_message_content(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $target = $this->user();
        $old = Conversation::factory()->create(['created_at' => now()->subDays(3)]);
        $this->participation($old, $me);
        $sender = $this->participation($old, $target);
        Message::factory()->create(['sender_participant_id' => $sender->id, 'conversation_id' => $old->id, 'accepted_at' => now()->subHour()]);
        $latest = Message::factory()->create(['sender_participant_id' => $sender->id, 'conversation_id' => $old->id, 'accepted_at' => now(), 'encrypted_body' => 'ciphertext-never-returned']);
        $group = Conversation::factory()->group()->create(['created_at' => now()->subDay()]);
        $this->participation($group, $me);
        $this->participation($group, $target);
        $this->participation($group, $this->user(), true);
        $archived = Conversation::factory()->create();
        $this->participation($archived, $me, true);
        Conversation::factory()->create();
        $response = $this->api('GET', '/api/conversations')->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $old->id)->assertJsonPath('data.0.latest_message.id', $latest->id)
            ->assertJsonPath('data.0.latest_message.sender.user.id', $target->id)
            ->assertJsonPath('data.1.kind', 'group')->assertJsonPath('meta.per_page', 20)
            ->assertJsonCount(1, 'data.0.participants')->assertJsonCount(1, 'data.1.participants');
        $this->assertStringNotContainsString('encrypted_body', $response->getContent());
        $this->assertStringNotContainsString('ciphertext-never-returned', $response->getContent());
        $this->assertStringNotContainsString('email', $response->getContent());
    }

    public function test_list_paginates_with_deterministic_tie_order(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $last = null;
        for ($i = 0; $i < 22; $i++) {
            $last = Conversation::factory()->create(['created_at' => '2026-11-01 10:00:00']);
            $this->participation($last, $me);
        }
        $this->api('GET', '/api/conversations')->assertJsonCount(20, 'data')->assertJsonPath('data.0.id', $last->id)->assertJsonPath('meta.total', 22);
        $this->api('GET', '/api/conversations?page=2')->assertJsonCount(2, 'data');
    }

    public function test_latest_message_uses_acceptance_time_and_id_as_tiebreaker(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $conversation = Conversation::factory()->create();
        $sender = $this->participation($conversation, $me);
        $timestamp = now();
        Message::factory()->create(['sender_participant_id' => $sender->id, 'conversation_id' => $conversation->id, 'accepted_at' => $timestamp]);
        $latest = Message::factory()->create(['sender_participant_id' => $sender->id, 'conversation_id' => $conversation->id, 'accepted_at' => $timestamp]);
        Message::factory()->create(['sender_participant_id' => $sender->id, 'conversation_id' => $conversation->id, 'accepted_at' => now()->subHour()]);
        $this->api('GET', '/api/conversations/'.$conversation->id)->assertOk()->assertJsonPath('data.latest_message.id', $latest->id);
    }

    public function test_show_and_delete_hide_conversations_from_nonparticipants_and_former_participants_even_admins(): void
    {
        $me = $this->user(['is_admin' => true]);
        $this->signIn($me);
        $stranger = Conversation::factory()->create();
        $left = Conversation::factory()->create();
        $this->participation($left, $me, true);
        foreach ([$stranger->id, $left->id, 999999] as $id) {
            $this->api('GET', '/api/conversations/'.$id)->assertNotFound();
            $this->api('DELETE', '/api/conversations/'.$id)->assertNotFound();
        }
    }

    public function test_archive_only_ends_own_participation_and_retains_history(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $target = $this->user();
        $id = $this->api('POST', '/api/conversations', ['user_id' => $target->id])->json('data.id');
        $conversation = Conversation::findOrFail($id);
        $sender = $conversation->activeParticipantFor($me);
        Message::factory()->create(['sender_participant_id' => $sender->id, 'conversation_id' => $id]);
        $this->api('DELETE', '/api/conversations/'.$id)->assertOk();
        $this->assertNotNull($sender->refresh()->left_at);
        $this->assertNotNull($conversation->activeParticipantFor($target));
        $this->assertDatabaseCount('messages', 1);
        $this->assertDatabaseCount('conversations', 1);
        $this->api('GET', '/api/conversations')->assertJsonCount(0, 'data');
        $this->api('GET', '/api/conversations/'.$id)->assertNotFound();
        $this->signIn($target);
        $this->api('GET', '/api/conversations/'.$id)->assertOk();
    }

    public function test_reopen_reuses_chat_with_new_participation_and_does_not_reopen_for_other_user(): void
    {
        $me = $this->user();
        $target = $this->user();
        $this->signIn($me);
        $id = $this->api('POST', '/api/conversations', ['user_id' => $target->id])->json('data.id');
        $conversation = Conversation::findOrFail($id);
        $original = $conversation->activeParticipantFor($me);
        $this->api('DELETE', '/api/conversations/'.$id)->assertOk();
        $this->signIn($target);
        $this->api('POST', '/api/conversations', ['user_id' => $me->id])->assertOk();
        $this->assertNull($conversation->activeParticipantFor($me));
        $this->api('DELETE', '/api/conversations/'.$id)->assertOk();
        $this->signIn($me);
        $this->api('POST', '/api/conversations', ['user_id' => $target->id])->assertOk()->assertJsonPath('data.id', $id);
        $this->assertNotSame($original->id, $conversation->activeParticipantFor($me)->id);
        $this->assertNotNull($original->refresh()->left_at);
        $this->assertNull($conversation->activeParticipantFor($target));
        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_group_delete_requires_group_leave_flow_and_does_not_mutate_participation(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $conversation = Conversation::factory()->group()->create();
        $participant = $this->participation($conversation, $me);
        $this->api('DELETE', '/api/conversations/'.$conversation->id)->assertUnprocessable()->assertJsonValidationErrors('conversation');
        $this->assertNull($participant->refresh()->left_at);
        $this->api('GET', '/api/conversations/'.$conversation->id)->assertOk()->assertJsonPath('data.kind', 'group');
    }

    public function test_policy_uses_active_participant_as_authority(): void
    {
        $user = $this->user();
        $conversation = Conversation::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('view', $conversation));
        $participant = $this->participation($conversation, $user);
        $this->assertTrue(Gate::forUser($user)->allows('view', $conversation));
        $this->assertTrue(Gate::forUser($user)->allows('delete', $conversation));
        $participant->update(['left_at' => now()]);
        $this->assertFalse(Gate::forUser($user)->allows('delete', $conversation));
    }

    public function test_deactivated_participant_does_not_break_conversation_or_expose_its_profile(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $contact = $this->user();
        $conversation = Conversation::factory()->create();
        $this->participation($conversation, $me);
        $sender = $this->participation($conversation, $contact);
        $message = Message::factory()->create(['sender_participant_id' => $sender->id, 'conversation_id' => $conversation->id]);
        $contact->delete();
        $this->api('GET', '/api/conversations/'.$conversation->id)->assertOk()
            ->assertJsonPath('data.participants.0.user', null)
            ->assertJsonPath('data.latest_message.id', $message->id)
            ->assertJsonPath('data.latest_message.sender.user', null);
        $this->api('GET', '/api/conversations')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_list_rejects_invalid_page(): void
    {
        $this->signIn($this->user());
        $this->api('GET', '/api/conversations?page=abc')->assertUnprocessable()->assertJsonValidationErrors('page');
    }
}
