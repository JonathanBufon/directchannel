<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;

final class StoreReportAction
{
    public function execute(User $user, array $data): Report
    {
        return Report::create([
            'reporter_user_id' => $user->id,
            'reported_user_id' => $data['reported_user_id'],
            'reason' => $data['reason'],
            'narrative' => $data['narrative'] ?? null,
            'status' => ReportStatus::Received,
            'created_at' => now(),
        ]);
    }
}
