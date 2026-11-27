<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Attachment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['message_id', 'storage_key', 'size_bytes'];

    protected function casts(): array
    {
        return [
            'message_id' => 'integer',
            'size_bytes' => 'integer',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id');
    }
}
