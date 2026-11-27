<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListOwnReportsAction
{
    public function execute(User $user): LengthAwarePaginator
    {
        return $user->reportsMade()->orderByDesc('id')->paginate(20);
    }
}
