<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListReportsAction
{
    public function execute(array $filters): LengthAwarePaginator
    {
        $query = Report::query();
        if (($filters['status'] ?? null) !== null) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('id')->paginate(20)->withQueryString();
    }
}
