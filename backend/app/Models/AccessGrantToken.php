<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\UsesMicrosecondTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AccessGrantToken extends Model
{
    use HasFactory, UsesMicrosecondTimestamps;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'personal_access_token_id';

    protected $fillable = ['personal_access_token_id', 'access_grant_id'];

    protected function casts(): array
    {
        return [
            'personal_access_token_id' => 'integer',
            'access_grant_id' => 'integer',
        ];
    }

    public function personalAccessToken(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class, 'personal_access_token_id');
    }

    public function accessGrant(): BelongsTo
    {
        return $this->belongsTo(AccessGrant::class, 'access_grant_id');
    }
}
