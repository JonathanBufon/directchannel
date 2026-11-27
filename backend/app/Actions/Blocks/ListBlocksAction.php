<?php

declare(strict_types=1);

namespace App\Actions\Blocks;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListBlocksAction
{
    public function execute(User $user): LengthAwarePaginator
    {
        return User::withTrashed()->whereHas('blocksReceived', fn (Builder $query): Builder => $query->where('blocker_user_id', $user->id))
            ->orderBy('name')->orderBy('id')->paginate(20);
    }
}
