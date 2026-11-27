<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\AccountBlock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class BlockAccountAction
{
    public function execute(User $user, User $admin, string $reason): AccountBlock
    {
        return DB::transaction(function () use ($user, $admin, $reason): AccountBlock {
            // Match the lock used by login; the account may have no block rows yet.
            $locked = User::withTrashed()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($locked->id === $admin->id) {
                throw ValidationException::withMessages(['user' => 'Você não pode bloquear sua própria conta.']);
            }
            if ($locked->isBlocked()) {
                throw ValidationException::withMessages(['user' => 'Esta conta já está bloqueada.']);
            }

            return $locked->accountBlocks()->create([
                'blocked_by_user_id' => $admin->id, 'reason' => $reason, 'blocked_at' => now(),
            ]);
        });
    }
}
