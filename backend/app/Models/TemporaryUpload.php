<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\UsesMicrosecondTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TemporaryUpload extends Model
{
    use HasFactory, UsesMicrosecondTimestamps;

    public $timestamps = false;

    protected $fillable = ['access_grant_id', 'user_id', 'conversation_id', 'storage_key', 'original_name', 'mime_type', 'size_bytes', 'expires_at', 'consumed_at', 'created_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'consumed_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'access_grant_id' => 'integer',
            'user_id' => 'integer',
            'conversation_id' => 'integer',
            'size_bytes' => 'integer',
        ];
    }

    public function accessGrant(): BelongsTo
    {
        return $this->belongsTo(AccessGrant::class, 'access_grant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }
}
