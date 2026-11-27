<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AccountBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'user_id' => $this->user_id, 'reason' => $this->reason,
            'blocked_by_user_id' => $this->blocked_by_user_id,
            'blocked_at' => $this->blocked_at->toISOString(),
            'unblocked_by_user_id' => $this->unblocked_by_user_id,
            'unblocked_at' => $this->unblocked_at?->toISOString(),
        ];
    }
}
