<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class ListGroupsAction
{
    public function execute(User $user): Collection
    {
        return Group::whereHas('conversation.participants', fn ($query) => $query->active()->where('user_id', $user->id))
            ->with(['activeMembers.participant.user' => fn ($query) => $query->withTrashed()])
            ->orderByDesc('id')->get();
    }
}
