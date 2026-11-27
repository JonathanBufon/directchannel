<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TemporaryUpload extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'conversation_id', 'storage_key', 'original_name', 'mime_type', 'size_bytes', 'expires_at', 'consumed_at', 'created_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'consumed_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'user_id' => 'integer',
            'conversation_id' => 'integer',
            'size_bytes' => 'integer',
        ];
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
