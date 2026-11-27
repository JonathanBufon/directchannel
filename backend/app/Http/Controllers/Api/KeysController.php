<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Keys\CurrentKeyAction;
use App\Actions\Keys\PublishKeyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Keys\PublishKeyRequest;
use App\Http\Resources\Keys\PublicKeyResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

final class KeysController extends Controller
{
    public function store(PublishKeyRequest $request, PublishKeyAction $action): JsonResponse
    {
        return (new PublicKeyResource($action->execute($request->user(), $request->validated('public_payload'))))
            ->response()->setStatusCode(201);
    }

    public function current(User $user, CurrentKeyAction $action): PublicKeyResource
    {
        Gate::authorize('view', $user);

        return new PublicKeyResource($action->execute($user));
    }
}
