<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

final class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'read_receipts_enabled',
        'notification_preview_enabled',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
            'is_admin' => 'boolean',
            'read_receipts_enabled' => 'boolean',
            'notification_preview_enabled' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isBlocked(): bool
    {
        return $this->accountBlocks()->open()->exists();
    }

    public function participations(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class, 'user_id');
    }

    public function accessGrants(): HasMany
    {
        return $this->hasMany(AccessGrant::class, 'user_id');
    }

    public function accountBlocks(): HasMany
    {
        return $this->hasMany(AccountBlock::class, 'user_id');
    }

    public function blocksMade(): HasMany
    {
        return $this->hasMany(Block::class, 'blocker_user_id');
    }

    public function blocksReceived(): HasMany
    {
        return $this->hasMany(Block::class, 'blocked_user_id');
    }

    public function reportsMade(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_user_id');
    }

    public function reportsReceived(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    public function reportsReviewed(): HasMany
    {
        return $this->hasMany(Report::class, 'reviewer_user_id');
    }

    public function publicKeys(): HasMany
    {
        return $this->hasMany(PublicKeyMaterial::class, 'user_id');
    }

    public function temporaryUploads(): HasMany
    {
        return $this->hasMany(TemporaryUpload::class, 'user_id');
    }
}
