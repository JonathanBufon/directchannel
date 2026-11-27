<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class ConversationParticipant extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['conversation_id', 'user_id', 'joined_at', 'left_at'];

    protected function casts(): array
    {
        return [
            'joined_at' => 'immutable_datetime',
            'left_at' => 'immutable_datetime',
            'conversation_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function groupMember(): HasOne
    {
        return $this->hasOne(GroupMember::class, 'participant_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_participant_id');
    }

    public function messageReceipts(): HasMany
    {
        return $this->hasMany(MessageReceipt::class, 'recipient_participant_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('left_at');
    }

    public function isActive(): bool
    {
        return $this->left_at === null;
    }
}
