<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $blocked = $this->resource->getAttribute('is_blocked') ?? $this->resource->isBlocked();

        return [
            'id' => $this->id, 'name' => $this->name, 'username' => $this->username,
            'email' => $this->email, 'is_admin' => $this->is_admin,
            'is_blocked' => (bool) $blocked,
            'status' => $this->deleted_at !== null ? 'desativados' : ($blocked ? 'bloqueados' : 'ativos'),
            'created_at' => $this->created_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
