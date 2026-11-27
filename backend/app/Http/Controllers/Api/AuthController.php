<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Actions\Auth\RegisterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return response()->json(['data' => ['token' => $result['token'], 'user' => new UserResource($result['user'])]], 201);
    }

    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $data = $request->validated();
        $result = $action->execute($data['email'], $data['password'], $data['device_name']);

        return response()->json(['data' => ['token' => $result['token'], 'user' => new UserResource($result['user'])]]);
    }

    public function logout(Request $request, LogoutAction $action): JsonResponse
    {
        $action->execute($request->user());

        return response()->json(['data' => ['message' => 'Sessão encerrada.']]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
