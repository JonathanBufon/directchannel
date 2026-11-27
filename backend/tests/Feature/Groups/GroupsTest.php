<?php

declare(strict_types=1);

namespace Tests\Feature\Groups;

use App\Enums\ConversationKind;
use App\Models\AccessGrant;
use App\Models\AccessGrantToken;
use App\Models\AccountBlock;
use App\Models\ConversationParticipant;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class GroupsTest extends TestCase
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

    private function member(Group $group, bool $responsible = false): ConversationParticipant
    {
        $participant = ConversationParticipant::factory()->create(['conversation_id' => $group->conversation_id, 'joined_at' => now()->subDay()]);
        GroupMember::create(['group_id' => $group->id, 'participant_id' => $participant->id, 'is_responsible' => $responsible]);

        return $participant;
    }

    private function group(): array
    {
        $group = Group::factory()->create();
        $owner = $this->member($group, true);
        $member = $this->member($group);

        return [$group, $owner, $member];
    }

    public function test_create_group_creates_coherent_memberships_and_creator_is_responsible(): void
    {
        $creator = User::factory()->create();
        $members = User::factory()->count(2)->create();
        $this->signIn($creator);
        $response = $this->postJson('/api/groups', ['name' => 'Equipe do projeto', 'member_ids' => $members->modelKeys()])
            ->assertCreated()->assertJsonPath('data.name', 'Equipe do projeto')
            ->assertJsonPath('data.member_count', 3)->assertJsonPath('data.is_responsible', true);
        $group = Group::findOrFail($response->json('data.id'));
        $this->assertSame(ConversationKind::Group, $group->conversation->kind);
        $this->assertTrue($group->isResponsible($creator));
        $this->assertDatabaseCount('group_members', 3);
        foreach ($members as $user) {
            $this->assertNotNull($group->conversation->activeParticipantFor($user));
            $this->assertFalse($group->isResponsible($user));
        }
        $this->assertArrayNotHasKey('email', $response->json('data.members.0'));
    }

    public static function invalidGroups(): array
    {
        return [
            'missing name' => [['member_ids' => [1]]],
            'short name' => [['name' => 'ab', 'member_ids' => [1]]],
            'long name' => [['name' => str_repeat('a', 101), 'member_ids' => [1]]],
            'missing members' => [['name' => 'Equipe']],
            'empty members' => [['name' => 'Equipe', 'member_ids' => []]],
            'duplicate members' => [['name' => 'Equipe', 'member_ids' => [1, 1]]],
            'bad member id' => [['name' => 'Equipe', 'member_ids' => ['bad']]],
            'many members' => [['name' => 'Equipe', 'member_ids' => range(1, 256)]],
        ];
    }

    #[DataProvider('invalidGroups')]
    public function test_group_creation_validation_is_atomic(array $payload): void
    {
        $this->signIn(User::factory()->create());
        $this->postJson('/api/groups', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('groups', 0);
        $this->assertDatabaseCount('conversations', 0);
        $this->assertDatabaseCount('conversation_participants', 0);
    }

    public function test_creator_duplicate_missing_deleted_and_blocked_accounts_are_ineligible(): void
    {
        $creator = User::factory()->create();
        $deleted = User::factory()->create();
        $deleted->delete();
        $blocked = User::factory()->create();
        AccountBlock::factory()->create(['user_id' => $blocked->id]);
        $this->signIn($creator);
        foreach ([$creator->id, $deleted->id, $blocked->id, 999999] as $id) {
            $this->postJson('/api/groups', ['name' => 'Equipe', 'member_ids' => [$id]])->assertUnprocessable();
        }
        $this->assertDatabaseCount('groups', 0);
    }

    public function test_lists_only_active_groups_and_details_only_active_members_with_roles(): void
    {
        [$group, $owner, $member] = $this->group();
        $former = $this->member($group, true);
        $former->update(['left_at' => now()]);
        Group::factory()->create();
        $this->signIn($member->user);
        $this->getJson('/api/groups')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $group->id)->assertJsonPath('data.0.is_responsible', false);
        $response = $this->getJson("/api/groups/$group->id")->assertOk()->assertJsonCount(2, 'data.members')->assertJsonPath('data.member_count', 2);
        $roles = collect($response->json('data.members'))->keyBy('id');
        $this->assertTrue($roles[$owner->user_id]['is_responsible']);
        $this->assertFalse($roles[$member->user_id]['is_responsible']);
        $this->signIn($former->user);
        $this->getJson('/api/groups')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/groups/$group->id")->assertNotFound();
    }

    public function test_responsible_can_rename_but_invalid_names_are_rejected(): void
    {
        [$group, $owner] = $this->group();
        $this->signIn($owner->user);
        $this->patchJson("/api/groups/$group->id", ['name' => 'Novo nome'])->assertOk()->assertJsonPath('data.name', 'Novo nome');
        foreach (['', 'ab', str_repeat('a', 101)] as $name) {
            $this->patchJson("/api/groups/$group->id", ['name' => $name])->assertUnprocessable()->assertJsonValidationErrors('name');
        }
        $this->assertSame('Novo nome', $group->refresh()->name);
    }

    public function test_add_members_and_reentry_create_new_participation_without_previous_responsibility(): void
    {
        [$group, $owner, $member] = $this->group();
        $member->groupMember->update(['is_responsible' => true]);
        $this->signIn($owner->user);
        $this->deleteJson("/api/groups/$group->id/members/$member->user_id")->assertNoContent();
        $this->assertNotNull($member->refresh()->left_at);
        $new = User::factory()->create();
        $this->postJson("/api/groups/$group->id/members", ['member_ids' => [$member->user_id, $new->id]])
            ->assertOk()->assertJsonPath('data.member_count', 3);
        $reentry = $group->conversation->activeParticipantFor($member->user);
        $this->assertNotSame($member->id, $reentry->id);
        $this->assertFalse($reentry->groupMember->is_responsible);
        $this->assertGreaterThan($member->joined_at, $reentry->joined_at);
        $this->assertDatabaseCount('conversation_participants', 4);
    }

    public function test_add_members_validates_input_active_accounts_and_existing_members_and_rolls_back(): void
    {
        [$group, $owner, $member] = $this->group();
        $this->signIn($owner->user);
        $new = User::factory()->create();
        $deleted = User::factory()->create();
        $deleted->delete();
        $blocked = User::factory()->create();
        AccountBlock::factory()->create(['user_id' => $blocked->id]);
        foreach ([[], [$owner->user_id], [$new->id, $new->id], ['bad'], [$deleted->id], [$blocked->id], [999999], [$new->id, $member->user_id]] as $ids) {
            $this->postJson("/api/groups/$group->id/members", ['member_ids' => $ids])->assertUnprocessable();
            $this->assertNull($group->conversation->activeParticipantFor($new));
            $this->assertSame(2, $group->activeMembers()->count());
        }
    }

    public function test_removal_preserves_history_and_denies_removed_members_access(): void
    {
        [$group, $owner, $member] = $this->group();
        $message = Message::factory()->create(['conversation_id' => $group->conversation_id, 'sender_participant_id' => $member->id]);
        $this->signIn($owner->user);
        $this->deleteJson("/api/groups/$group->id/members/$member->user_id")->assertNoContent();
        $this->assertNotNull($member->refresh()->left_at);
        $this->assertDatabaseHas('messages', ['id' => $message->id]);
        $this->assertDatabaseHas('group_members', ['participant_id' => $member->id]);
        $this->signIn($member->user);
        $this->getJson("/api/groups/$group->id")->assertNotFound();
        $this->getJson("/api/conversations/$group->conversation_id/messages")->assertNotFound();
    }

    public function test_cannot_remove_last_responsible_while_other_members_remain(): void
    {
        [$group, $owner] = $this->group();
        $this->signIn($owner->user);
        $this->deleteJson("/api/groups/$group->id/members/$owner->user_id")->assertUnprocessable();
        $this->assertNull($owner->refresh()->left_at);
    }

    public function test_promote_and_demote_allow_multiple_responsibles_but_never_zero(): void
    {
        [$group, $owner, $member] = $this->group();
        $this->signIn($owner->user);
        $this->deleteJson("/api/groups/$group->id/responsibles/$owner->user_id")->assertUnprocessable();
        $this->postJson("/api/groups/$group->id/responsibles/$member->user_id")->assertNoContent();
        $this->assertSame(2, $group->activeMembers()->where('is_responsible', true)->count());
        $this->postJson("/api/groups/$group->id/responsibles/$member->user_id")->assertNoContent();
        $this->deleteJson("/api/groups/$group->id/responsibles/$owner->user_id")->assertNoContent();
        $this->assertFalse($group->isResponsible($owner->user));
        $this->signIn($member->user);
        $this->deleteJson("/api/groups/$group->id/responsibles/$member->user_id")->assertUnprocessable();
    }

    public function test_last_responsible_must_select_active_successor_and_transfer_atomically(): void
    {
        [$group, $owner, $member] = $this->group();
        $former = $this->member($group, true);
        $former->update(['left_at' => now()]);
        $outsider = User::factory()->create();
        $this->signIn($owner->user);
        foreach ([[], ['successor_user_id' => $owner->user_id], ['successor_user_id' => $outsider->id], ['successor_user_id' => $former->user_id], ['successor_user_id' => 'bad']] as $payload) {
            $this->postJson("/api/groups/$group->id/leave", $payload)->assertUnprocessable()->assertJsonValidationErrors('successor_user_id');
            $this->assertNull($owner->refresh()->left_at);
            $this->assertFalse($group->isResponsible($member->user));
        }
        $this->postJson("/api/groups/$group->id/leave", ['successor_user_id' => $member->user_id])->assertNoContent();
        $this->assertNotNull($owner->refresh()->left_at);
        $this->assertTrue($group->isResponsible($member->user));
        $this->getJson("/api/groups/$group->id")->assertNotFound();
        $this->signIn($member->user);
        $this->getJson("/api/groups/$group->id")->assertOk()->assertJsonPath('data.is_responsible', true)->assertJsonPath('data.member_count', 1);
    }

    public function test_regular_member_and_one_of_multiple_responsibles_can_leave_without_successor(): void
    {
        [$group, $owner, $member] = $this->group();
        $secondOwner = $this->member($group, true);
        $this->signIn($member->user);
        $this->postJson("/api/groups/$group->id/leave")->assertNoContent();
        $this->signIn($owner->user);
        $this->postJson("/api/groups/$group->id/leave")->assertNoContent();
        $this->assertTrue($group->isResponsible($secondOwner->user));
        $this->assertSame(1, $group->activeMembers()->count());
    }

    public function test_last_member_cannot_leave_without_successor_and_can_explicitly_close_group(): void
    {
        $group = Group::factory()->create();
        $owner = $this->member($group, true);
        $message = Message::factory()->create(['conversation_id' => $group->conversation_id, 'sender_participant_id' => $owner->id]);
        $this->signIn($owner->user);
        $this->postJson("/api/groups/$group->id/leave")->assertUnprocessable()->assertJsonValidationErrors('successor_user_id');
        $this->assertNull($owner->refresh()->left_at);
        $this->assertSame(1, $group->activeMembers()->count());
        $this->deleteJson("/api/groups/$group->id/members/$owner->user_id")->assertUnprocessable();
        $this->assertNull($owner->refresh()->left_at);
        $this->deleteJson("/api/groups/$group->id")->assertNoContent();
        $this->assertSame(0, $group->activeMembers()->count());
        $this->assertDatabaseHas('groups', ['id' => $group->id]);
        $this->assertDatabaseHas('messages', ['id' => $message->id]);
        $this->getJson('/api/groups')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/groups/$group->id")->assertNotFound();
    }

    public function test_responsible_can_end_group_preserving_all_historical_records(): void
    {
        [$group, $owner, $member] = $this->group();
        $message = Message::factory()->create(['conversation_id' => $group->conversation_id, 'sender_participant_id' => $owner->id]);
        $this->signIn($owner->user);
        $this->deleteJson("/api/groups/$group->id")->assertNoContent();
        $this->assertNotNull($owner->refresh()->left_at);
        $this->assertNotNull($member->refresh()->left_at);
        $this->assertDatabaseHas('messages', ['id' => $message->id]);
        $this->assertDatabaseCount('group_members', 2);
        $this->getJson("/api/groups/$group->id")->assertNotFound();
        $this->postJson("/api/groups/$group->id/members", ['member_ids' => [User::factory()->create()->id]])->assertNotFound();
    }

    public function test_non_members_and_former_members_cannot_access_group_endpoints_even_as_admin(): void
    {
        [$group, $owner, $member] = $this->group();
        $member->update(['left_at' => now()]);
        foreach ([$member->user, User::factory()->admin()->create()] as $user) {
            $this->signIn($user);
            $this->getJson("/api/groups/$group->id")->assertNotFound();
            foreach ($this->mutationRoutes($group, $owner->user_id) as [$method, $uri]) {
                $this->json($method, $uri, ['name' => 'Novo nome', 'member_ids' => [User::factory()->create()->id]])->assertNotFound();
            }
        }
    }

    public function test_regular_member_cannot_manage_group(): void
    {
        [$group, $owner, $member] = $this->group();
        $this->signIn($member->user);
        foreach ($this->mutationRoutes($group, $owner->user_id) as [$method, $uri]) {
            if (str_ends_with($uri, '/leave')) {
                continue;
            }
            $this->json($method, $uri, ['name' => 'Novo nome', 'member_ids' => [User::factory()->create()->id]])->assertForbidden();
        }
        $this->assertSame(2, $group->activeMembers()->count());
    }

    public function test_membership_mutations_reject_targets_outside_the_group(): void
    {
        [$group, $owner] = $this->group();
        $outsider = User::factory()->create();
        $former = $this->member($group);
        $former->update(['left_at' => now()]);
        $this->signIn($owner->user);
        foreach ([$outsider->id, $former->user_id] as $id) {
            $this->deleteJson("/api/groups/$group->id/members/$id")->assertNotFound();
            $this->postJson("/api/groups/$group->id/responsibles/$id")->assertNotFound();
            $this->deleteJson("/api/groups/$group->id/responsibles/$id")->assertNotFound();
        }
    }

    public function test_reentry_hides_previous_messages_and_receipts(): void
    {
        [$group, $owner, $member] = $this->group();
        $this->signIn($owner->user);
        $old = $this->postJson("/api/conversations/$group->conversation_id/messages", ['encrypted_body' => 'YQ=='])->assertCreated()->json('data.id');
        $this->deleteJson("/api/groups/$group->id/members/$member->user_id")->assertNoContent();
        $this->postJson("/api/groups/$group->id/members", ['member_ids' => [$member->user_id]])->assertOk();
        $new = $this->postJson("/api/conversations/$group->conversation_id/messages", ['encrypted_body' => 'Yg=='])->assertCreated()->json('data.id');
        $this->signIn($member->user);
        $this->getJson("/api/conversations/$group->conversation_id/messages")->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $new);
        $this->postJson("/api/messages/$old/read")->assertNotFound();
        $this->postJson("/api/messages/$new/read")->assertNoContent();
    }

    public function test_non_numeric_group_route_parameters_return_404(): void
    {
        [$group, $owner] = $this->group();
        $this->signIn($owner->user);
        $this->getJson('/api/groups/invalid')->assertNotFound();
        $this->patchJson('/api/groups/invalid')->assertNotFound();
        $this->deleteJson('/api/groups/invalid')->assertNotFound();
        $this->postJson('/api/groups/invalid/leave')->assertNotFound();
        $this->postJson('/api/groups/invalid/members')->assertNotFound();
        $this->deleteJson("/api/groups/$group->id/members/invalid")->assertNotFound();
        $this->postJson("/api/groups/$group->id/responsibles/invalid")->assertNotFound();
        $this->deleteJson("/api/groups/$group->id/responsibles/invalid")->assertNotFound();
    }

    public function test_all_group_endpoints_require_authentication(): void
    {
        [$group, $owner] = $this->group();
        foreach ([['GET', '/api/groups'], ['POST', '/api/groups'], ['GET', "/api/groups/$group->id"], ...$this->mutationRoutes($group, $owner->user_id)] as [$method, $uri]) {
            $this->json($method, $uri)->assertUnauthorized();
        }
    }

    private function mutationRoutes(Group $group, int $target): array
    {
        return [
            ['PATCH', "/api/groups/$group->id"],
            ['POST', "/api/groups/$group->id/members"],
            ['DELETE', "/api/groups/$group->id/members/$target"],
            ['POST', "/api/groups/$group->id/responsibles/$target"],
            ['DELETE', "/api/groups/$group->id/responsibles/$target"],
            ['POST', "/api/groups/$group->id/leave"],
            ['DELETE', "/api/groups/$group->id"],
        ];
    }
}
