<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Report;
use Illuminate\Support\Facades\DB;

final class ArchiveReportAction
{
    public function execute(Report $report): void
    {
        DB::transaction(function () use ($report): void {
            Report::whereKey($report->id)->lockForUpdate()->firstOrFail()->delete();
        });
    }
}
