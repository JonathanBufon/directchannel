<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AddGroupMembersAction
{
    public function __construct(private readonly GroupContext $context) {}

    public function execute(Group $group, User $actor, array $memberIds): Group
    {
        return DB::transaction(function () use ($group, $actor, $memberIds): Group {
            $group = $this->context->lock($group, $actor);
            $users = $this->context->eligibleUsers($memberIds);
            foreach ($users as $user) {
                if ($group->conversation->activeParticipantFor($user) !== null) {
                    throw ValidationException::withMessages(['member_ids' => 'Uma das contas já participa deste grupo.']);
                }
                $this->context->join($group, $user);
            }

            return $this->context->details($group);
        }, 3);
    }
}
