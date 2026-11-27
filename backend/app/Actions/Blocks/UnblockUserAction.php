<?php

declare(strict_types=1);

namespace App\Actions\Blocks;

use App\Models\Block;
use App\Models\User;

final class UnblockUserAction
{
    public function execute(User $user, User $target): void
    {
        Block::where('blocker_user_id', $user->id)->where('blocked_user_id', $target->id)->delete();
    }
}
