<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Group extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['conversation_id', 'name'];

    protected function casts(): array
    {
        return [
            'conversation_id' => 'integer',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function groupMembers(): HasMany
    {
        return $this->hasMany(GroupMember::class, 'group_id');
    }

    public function activeMembers(): HasMany
    {
        return $this->groupMembers()->whereHas('participant', fn ($query) => $query->active());
    }

    public function isResponsible(User $user): bool
    {
        $participant = $this->conversation->activeParticipantFor($user);

        return $participant !== null && $this->groupMembers()
            ->where('participant_id', $participant->id)->where('is_responsible', true)->exists();
    }
}
