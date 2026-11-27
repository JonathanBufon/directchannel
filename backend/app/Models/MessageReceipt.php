<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Composite primary key: use query builder with every key in where() for updates/deletes. */
final class MessageReceipt extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = ['message_id', 'recipient_participant_id', 'delivered_at', 'read_at'];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'immutable_datetime',
            'read_at' => 'immutable_datetime',
            'message_id' => 'integer',
            'recipient_participant_id' => 'integer',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(ConversationParticipant::class, 'recipient_participant_id');
    }

    public function recipientParticipant(): BelongsTo
    {
        return $this->belongsTo(ConversationParticipant::class, 'recipient_participant_id');
    }
}
