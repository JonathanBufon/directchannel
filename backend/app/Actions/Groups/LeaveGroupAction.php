<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class LeaveGroupAction
{
    public function __construct(private readonly GroupContext $context) {}

    public function execute(Group $group, User $actor, ?int $successorUserId): void
    {
        DB::transaction(function () use ($group, $actor, $successorUserId): void {
            $group = $this->context->lock($group, $actor, 'view');
            $member = $this->context->member($group, $actor);
            $members = $group->activeMembers()->with('participant')->orderBy('participant_id')->lockForUpdate()->get();
            $needsSuccessor = $member->is_responsible && $members->count() > 1 && $members->where('is_responsible', true)->count() === 1;
            if ($needsSuccessor) {
                $successor = $members->first(fn ($candidate): bool => $candidate->participant->user_id === $successorUserId && $candidate->participant_id !== $member->participant_id);
                if ($successor === null) {
                    throw ValidationException::withMessages(['successor_user_id' => 'Escolha outro membro ativo para assumir a responsabilidade antes de sair.']);
                }
                $successor->update(['is_responsible' => true]);
            }
            $this->context->endParticipation($member->participant_id);
        }, 3);
    }
}
