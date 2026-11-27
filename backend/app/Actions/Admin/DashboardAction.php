<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class DashboardAction
{
    public function execute(): array
    {
        return [
            'active_users' => User::whereDoesntHave('accountBlocks', fn (Builder $query): Builder => $query->open())->count(),
            'received_reports' => Report::received()->count(),
            'blocked_accounts' => User::withTrashed()->whereHas('accountBlocks', fn (Builder $query): Builder => $query->open())->count(),
        ];
    }
}
