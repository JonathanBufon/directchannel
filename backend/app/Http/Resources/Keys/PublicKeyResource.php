<?php

declare(strict_types=1);

namespace App\Http\Resources\Keys;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PublicKeyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'user_id' => $this->user_id,
            'public_payload' => base64_encode($this->public_payload),
            'published_at' => $this->published_at,
            'fingerprint' => hash('sha256', $this->public_payload),
        ];
    }
}
