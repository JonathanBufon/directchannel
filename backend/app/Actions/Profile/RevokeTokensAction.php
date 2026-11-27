<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\AccessGrantToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class RevokeTokensAction
{
    /** Call within a transaction holding the user's row lock, as login does. */
    public function execute(User $user, ?int $keepTokenId = null): void
    {
        $tokens = $user->tokens()->when($keepTokenId !== null, fn (Builder $query): Builder => $query->where('id', '<>', $keepTokenId));
        $ids = $tokens->pluck('id');
        AccessGrantToken::whereIn('personal_access_token_id', $ids)->delete();
        $tokens->delete();
        $user->accessGrants()->active()->whereDoesntHave('accessGrantTokens')->update(['revoked_at' => now()]);
    }
}
