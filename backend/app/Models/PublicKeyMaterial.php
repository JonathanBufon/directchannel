<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Binary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PublicKeyMaterial extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'public_key_material';

    protected $fillable = ['user_id', 'public_payload', 'published_at'];

    protected $hidden = ['public_payload'];

    protected function casts(): array
    {
        return [
            'published_at' => 'immutable_datetime',
            'user_id' => 'integer',
            'public_payload' => Binary::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
