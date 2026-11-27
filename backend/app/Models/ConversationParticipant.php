<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\UsesMicrosecondTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class ConversationParticipant extends Model
{
    use HasFactory, UsesMicrosecondTimestamps;

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

    /** Apply the active participation boundary to a correlated messages query. */
    public function scopeSeeingMessage(Builder $query): Builder
    {
        return $query->active()
            ->whereColumn('conversation_participants.conversation_id', 'messages.conversation_id')
            ->whereColumn('messages.accepted_at', '>=', 'conversation_participants.joined_at');
    }

    public function canSee(Message $message): bool
    {
        return Message::query()->whereKey($message->id)
            ->whereExists($this->newQuery()->whereKey($this->id)->seeingMessage()->selectRaw('1'))
            ->exists();
    }

    public function isActive(): bool
    {
        return $this->left_at === null;
    }
}
