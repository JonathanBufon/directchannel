<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\UsesMicrosecondTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GroupMember extends Model
{
    use HasFactory, UsesMicrosecondTimestamps;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'participant_id';

    protected $fillable = ['participant_id', 'group_id', 'is_responsible'];

    protected function casts(): array
    {
        return [
            'participant_id' => 'integer',
            'group_id' => 'integer',
            'is_responsible' => 'boolean',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(ConversationParticipant::class, 'participant_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
