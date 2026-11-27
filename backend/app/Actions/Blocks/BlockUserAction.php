<?php

declare(strict_types=1);

namespace App\Actions\Blocks;

use App\Models\Block;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class BlockUserAction
{
    public function execute(User $user, int $targetId): User
    {
        return DB::transaction(function () use ($user, $targetId): User {
            // Same ordered locks as direct conversation creation, including reversed pairs.
            $pair = User::whereKey([$user->id, $targetId])->orderBy('id')->lockForUpdate()->get();
            $target = $pair->firstWhere('id', $targetId);
            abort_if($target === null || $pair->count() !== 2, 404, 'Usuário não encontrado.');
            Block::query()->insertOrIgnore(['blocker_user_id' => $user->id, 'blocked_user_id' => $targetId]);

            return $target;
        }, 3);
    }
}
