<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Password\ForgotPasswordAction;
use App\Actions\Password\ResetPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Password\ForgotPasswordRequest;
use App\Http\Requests\Password\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;

final class PasswordController extends Controller
{
    public function forgot(ForgotPasswordRequest $request, ForgotPasswordAction $action): JsonResponse
    {
        $action->execute($request->validated('email'));

        return response()->json(['data' => ['message' => 'Se o e-mail estiver cadastrado, você receberá as instruções para redefinir sua senha.']]);
    }

    public function reset(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        $action->execute($request->validated());

        return response()->json(['data' => ['message' => 'Senha redefinida com sucesso. Faça login com suas novas credenciais.']]);
    }
}
