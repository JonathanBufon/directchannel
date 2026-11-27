<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConversationKind;
use App\Models\Concerns\UsesMicrosecondTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Conversation extends Model
{
    use HasFactory, UsesMicrosecondTimestamps;

    public $timestamps = false;

    protected $fillable = ['kind', 'created_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'kind' => ConversationKind::class,
        ];
    }

    public function isGroup(): bool
    {
        return $this->kind === ConversationKind::Group;
    }

    /**
     * Participação ativa do usuário nesta conversa, ou null se ele não participa agora.
     * Ponto único de verdade para autorizar acesso a conversa, mensagens e arquivos.
     */
    public function activeParticipantFor(User $user): ?ConversationParticipant
    {
        return $this->participants()
            ->active()
            ->where('user_id', $user->getKey())
            ->first();
    }

    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'conversation_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id');
    }

    public function conversationParticipants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function temporaryUploads(): HasMany
    {
        return $this->hasMany(TemporaryUpload::class, 'conversation_id');
    }

    public function scopeWithVisibleDetails(Builder $query, User $user): Builder
    {
        return $query->with([
            'participants' => fn (HasMany $query): HasMany => $query->active()->orderBy('id'),
            'participants.user',
            'latestMessage' => fn (HasOne $query): HasOne => $query->visibleTo($user)
                ->select('messages.id', 'messages.conversation_id', 'messages.sender_participant_id', 'messages.accepted_at'),
            'latestMessage.sender.user',
            'group',
        ]);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'conversation_id')->ofMany(['accepted_at' => 'max', 'id' => 'max']);
    }
}
