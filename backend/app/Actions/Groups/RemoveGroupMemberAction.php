<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RemoveGroupMemberAction
{
    public function __construct(private readonly GroupContext $context) {}

    public function execute(Group $group, User $actor, User $target): void
    {
        DB::transaction(function () use ($group, $actor, $target): void {
            $group = $this->context->lock($group, $actor);
            $member = $this->context->member($group, $target);
            $this->context->ensureCanRemove($group, $member);
            $this->context->endParticipation($member->participant_id);
        }, 3);
    }
}
