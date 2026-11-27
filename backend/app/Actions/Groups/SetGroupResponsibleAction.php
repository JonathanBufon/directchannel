<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SetGroupResponsibleAction
{
    public function __construct(private readonly GroupContext $context) {}

    public function execute(Group $group, User $actor, User $target, bool $responsible): void
    {
        DB::transaction(function () use ($group, $actor, $target, $responsible): void {
            $group = $this->context->lock($group, $actor);
            $member = $this->context->member($group, $target);
            if (! $responsible && $member->is_responsible && $group->activeMembers()->where('is_responsible', true)->count() === 1) {
                throw ValidationException::withMessages(['user' => 'O grupo deve manter ao menos um responsável ativo.']);
            }
            $member->update(['is_responsible' => $responsible]);
        }, 3);
    }
}
