<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\UsesMicrosecondTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AccountBlock extends Model
{
    use HasFactory, UsesMicrosecondTimestamps;

    public $timestamps = false;

    protected $fillable = ['user_id', 'blocked_by_user_id', 'reason', 'blocked_at', 'unblocked_by_user_id', 'unblocked_at'];

    protected function casts(): array
    {
        return [
            'blocked_at' => 'immutable_datetime',
            'unblocked_at' => 'immutable_datetime',
            'user_id' => 'integer',
            'blocked_by_user_id' => 'integer',
            'unblocked_by_user_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by_user_id');
    }

    public function unblockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unblocked_by_user_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('unblocked_at');
    }

    public function isOpen(): bool
    {
        return $this->unblocked_at === null;
    }
}
