<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\AccountBlock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UnblockAccountAction
{
    public function execute(User $user, User $admin): AccountBlock
    {
        return DB::transaction(function () use ($user, $admin): AccountBlock {
            User::withTrashed()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $block = $user->accountBlocks()->open()->lockForUpdate()->first();
            if ($block === null) {
                throw ValidationException::withMessages(['user' => 'Esta conta não possui bloqueio aberto.']);
            }
            $block->update(['unblocked_by_user_id' => $admin->id, 'unblocked_at' => now()]);

            return $block;
        });
    }
}
