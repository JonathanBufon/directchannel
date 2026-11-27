<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Binary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Message extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['conversation_id', 'sender_participant_id', 'encrypted_body', 'accepted_at'];

    protected $hidden = ['encrypted_body'];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'immutable_datetime',
            'conversation_id' => 'integer',
            'sender_participant_id' => 'integer',
            'encrypted_body' => Binary::class,
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(ConversationParticipant::class, 'sender_participant_id');
    }

    public function senderParticipant(): BelongsTo
    {
        return $this->belongsTo(ConversationParticipant::class, 'sender_participant_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'message_id');
    }

    public function messageReceipts(): HasMany
    {
        return $this->hasMany(MessageReceipt::class, 'message_id');
    }
}
