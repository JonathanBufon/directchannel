<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // The final attachment schema has no plaintext name/MIME; never expose storage keys.
        return ['id' => $this->id, 'message_id' => $this->message_id, 'size_bytes' => $this->size_bytes];
    }
}
