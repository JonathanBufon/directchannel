<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class SearchUsersAction
{
    public function execute(User $viewer, string $search): LengthAwarePaginator
    {
        // Treat SQL wildcard characters as literal search text.
        $pattern = '%'.addcslashes($search, '\\%_').'%';

        return User::query()->whereKeyNot($viewer->id)
            ->whereDoesntHave('blocksMade', fn (Builder $query): Builder => $query->where('blocked_user_id', $viewer->id))
            ->where(fn (Builder $query): Builder => $query->where('username', 'ilike', $pattern)->orWhere('name', 'ilike', $pattern))
            ->orderBy('name')->orderBy('id')->paginate(20);
    }
}
