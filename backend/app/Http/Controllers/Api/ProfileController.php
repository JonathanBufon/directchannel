<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Profile\ChangePasswordAction;
use App\Actions\Profile\DeleteAccountAction;
use App\Actions\Profile\UpdateProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\DeleteAccountRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

final class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request, UpdateProfileAction $action): UserResource
    {
        return new UserResource($action->execute($request->user(), $request->validated()));
    }

    public function password(ChangePasswordRequest $request, ChangePasswordAction $action): JsonResponse
    {
        $action->execute($request->user(), $request->validated('current_password'), $request->validated('password'));

        return response()->json(['data' => ['message' => 'Senha alterada. As outras sessões foram encerradas.']]);
    }

    public function destroy(DeleteAccountRequest $request, DeleteAccountAction $action): JsonResponse
    {
        $action->execute($request->user(), $request->validated('password'));

        return response()->json(['data' => ['message' => 'Conta desativada.']]);
    }
}
