<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureActiveAccessGrant;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [EnsureActiveAccessGrant::class]);
        $middleware->appendToPriorityList(AuthenticatesRequests::class, EnsureActiveAccessGrant::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            $message = $exception->getMessage() === 'Unauthenticated.'
                ? 'Não autenticado. Faça login para continuar.'
                : $exception->getMessage();

            return response()->json(['message' => $message], 401);
        });
        $exceptions->render(function (ThrottleRequestsException $exception, Request $request): ?JsonResponse {
            return $request->is('api/*')
                ? response()->json(['message' => 'Muitas tentativas. Aguarde um minuto e tente novamente.'], 429, $exception->getHeaders())
                : null;
        });
        $exceptions->render(function (ValidationException $exception, Request $request): ?JsonResponse {
            return $request->is('api/*')
                ? response()->json(['message' => 'Os dados informados são inválidos.', 'errors' => $exception->errors()], $exception->status)
                : null;
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
