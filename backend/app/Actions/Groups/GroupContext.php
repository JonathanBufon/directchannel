<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class GroupContext
{
    /** Must be called inside the mutation's transaction. All group writers lock conversation first. */
    public function lock(Group $group, User $actor, string $ability = 'manage'): Group
    {
        $conversation = Conversation::whereKey($group->conversation_id)->lockForUpdate()->firstOrFail();
        $group = Group::whereKey($group->id)->lockForUpdate()->firstOrFail();
        $group->setRelation('conversation', $conversation);
        Gate::authorize($ability, $group);

        return $group;
    }

    public function details(Group $group): Group
    {
        return $group->load(['activeMembers.participant.user' => fn ($query) => $query->withTrashed()]);
    }

    public function eligibleUsers(array $userIds): Collection
    {
        $users = User::whereIn('id', $userIds)->orderBy('id')->lockForUpdate()->get();
        if ($users->count() !== count($userIds) || $users->contains(fn (User $user): bool => $user->isBlocked())) {
            throw ValidationException::withMessages(['member_ids' => 'Selecione apenas contas ativas e disponíveis.']);
        }

        return $users;
    }

    public function join(Group $group, User $user, bool $responsible = false): void
    {
        // Keep the precise reentry boundary even for messages in the same second.
        $participant = new ConversationParticipant;
        $participant->setDateFormat('Y-m-d H:i:s.uP');
        $participant->fill([
            'conversation_id' => $group->conversation_id,
            'user_id' => $user->id,
            'joined_at' => now(),
        ]);
        $participant->save();
        GroupMember::create(['participant_id' => $participant->id, 'group_id' => $group->id, 'is_responsible' => $responsible]);
    }

    public function member(Group $group, User $user): GroupMember
    {
        $participant = $group->conversation->activeParticipantFor($user);
        abort_if($participant === null, 404, 'Participante não encontrado.');

        return $group->activeMembers()->where('participant_id', $participant->id)->lockForUpdate()->firstOrFail();
    }

    public function ensureCanRemove(Group $group, GroupMember $member): void
    {
        if ($member->is_responsible && $group->activeMembers()->count() > 1 && $group->activeMembers()->where('is_responsible', true)->count() === 1) {
            throw ValidationException::withMessages(['user' => 'Promova outro responsável antes de remover o último responsável ativo.']);
        }
    }

    public function endParticipation(int $participantId): void
    {
        ConversationParticipant::whereKey($participantId)->active()->update(['left_at' => now()->format('Y-m-d H:i:s.uP')]);
    }
}
