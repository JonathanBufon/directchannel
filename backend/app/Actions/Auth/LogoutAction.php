<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\AccessGrant;
use App\Models\AccessGrantToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

final class LogoutAction
{
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user): void {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $token = $user->currentAccessToken();
            if (! $token instanceof PersonalAccessToken) {
                return;
            }
            $link = AccessGrantToken::find($token->getKey());
            if ($link === null) {
                return;
            }
            $grant = AccessGrant::whereKey($link->access_grant_id)->lockForUpdate()->firstOrFail();
            $link->delete();
            $token->delete();
            if ($grant->isActive() && ! $grant->accessGrantTokens()->exists()) {
                $grant->update(['revoked_at' => now()]);
            }
        }, 3);
    }
}
