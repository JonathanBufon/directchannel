<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reporter_user_id' => $this->reporter_user_id,
            'reported_user_id' => $this->reported_user_id,
            'reason' => $this->reason,
            'narrative' => $this->narrative,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'reviewer_user_id' => $this->reviewer_user_id,
            'decision' => $this->decision,
            'decided_at' => $this->decided_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
