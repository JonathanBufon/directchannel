<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

final class PersonalAccessToken extends \Laravel\Sanctum\PersonalAccessToken
{
    protected function casts(): array
    {
        return [
            'abilities' => 'json',
            'last_used_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function accessGrantToken(): HasOne
    {
        return $this->hasOne(AccessGrantToken::class, 'personal_access_token_id');
    }
}
