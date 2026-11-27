<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AccessGrantToken;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

final class EnsureActiveAccessGrant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Public API routes have no authenticated user. Protected routes run auth first.
        if ($request->user() !== null) {
            $token = $request->user()->currentAccessToken();
            $grant = $token instanceof PersonalAccessToken
                ? AccessGrantToken::find($token->getKey())?->accessGrant
                : null;
            if ($grant === null || ! $grant->isActive() || $grant->user_id !== $request->user()->id) {
                throw new AuthenticationException('Acesso revogado. Faça login novamente.');
            }
        }

        return $next($request);
    }
}
