<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TemporaryUploadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'size_bytes' => $this->size_bytes,
            'mime_type' => $this->mime_type,
            'original_name' => $this->original_name,
            'expires_at' => $this->expires_at->toISOString(),
        ];
    }
}
