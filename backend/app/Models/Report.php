<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reporter_user_id',
        'reported_user_id',
        'reason',
        'narrative',
        'status',
        'reviewer_user_id',
        'decision',
        'decided_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'reporter_user_id' => 'integer',
            'reported_user_id' => 'integer',
            'reviewer_user_id' => 'integer',
            'decided_at' => 'timestamp',
            'created_at' => 'timestamp',
            'deleted_at' => 'timestamp',
        ];
    }

    public function reporterUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewerUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
