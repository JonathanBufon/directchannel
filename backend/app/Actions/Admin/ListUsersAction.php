<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListUsersAction
{
    public function execute(array $filters): LengthAwarePaginator
    {
        $query = User::withTrashed()->withExists(['accountBlocks as is_blocked' => fn (Builder $query): Builder => $query->open()]);
        if (($filters['search'] ?? '') !== '') {
            // Escape SQL wildcards so a username search remains literal.
            $search = '%'.addcslashes($filters['search'], '\\%_').'%';
            $query->where(fn (Builder $query): Builder => $query->where('name', 'ilike', $search)->orWhere('username', 'ilike', $search));
        }
        match ($filters['status'] ?? null) {
            'ativos' => $query->whereNull('deleted_at')->whereDoesntHave('accountBlocks', fn (Builder $query): Builder => $query->open()),
            'desativados' => $query->onlyTrashed(),
            'bloqueados' => $query->whereHas('accountBlocks', fn (Builder $query): Builder => $query->open()),
            default => null,
        };

        return $query->orderBy('id')->paginate(20)->withQueryString();
    }
}
