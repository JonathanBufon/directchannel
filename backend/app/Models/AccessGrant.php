<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AccessGrant extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'started_at', 'revoked_at'];

    protected function casts(): array
    {
        return [
            'started_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'user_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function accessGrantTokens(): HasMany
    {
        return $this->hasMany(AccessGrantToken::class, 'access_grant_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }
}
