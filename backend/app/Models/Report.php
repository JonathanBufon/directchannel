<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Report extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = ['reporter_user_id', 'reported_user_id', 'reason', 'narrative', 'status', 'reviewer_user_id', 'decision', 'decided_at', 'created_at', 'deleted_at'];

    protected function casts(): array
    {
        return [
            'decided_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
            'reporter_user_id' => 'integer',
            'reported_user_id' => 'integer',
            'reviewer_user_id' => 'integer',
            'status' => ReportStatus::class,
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reported(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    public function scopeReceived(Builder $query): Builder
    {
        return $query->where('status', ReportStatus::Received);
    }

    public function isReceived(): bool
    {
        return $this->status === ReportStatus::Received;
    }
}
