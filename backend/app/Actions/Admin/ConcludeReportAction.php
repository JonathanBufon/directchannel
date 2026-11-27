<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ConcludeReportAction
{
    public function execute(Report $report, User $reviewer, string $decision): Report
    {
        return DB::transaction(function () use ($report, $reviewer, $decision): Report {
            $locked = Report::whereKey($report->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->isReceived(), 409, 'Esta denúncia já foi concluída.');
            $locked->update([
                'status' => ReportStatus::Concluded,
                'reviewer_user_id' => $reviewer->id,
                'decision' => $decision,
                'decided_at' => now(),
            ]);

            return $locked;
        });
    }
}
