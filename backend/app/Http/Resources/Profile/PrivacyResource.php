<?php

declare(strict_types=1);

namespace App\Http\Resources\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PrivacyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'read_receipts_enabled' => $this->read_receipts_enabled,
            'notification_preview_enabled' => $this->notification_preview_enabled,
        ];
    }
}
