<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Exception $e, Request $request) {
            $statusCode = 500;

            switch (true) {
                case $e instanceof NotFoundHttpException:
                    return response()->notFound('Ruta no encontrada');

                case $e instanceof ModelNotFoundException:
                    return response()->notFound('Recurso no encontrado');

                case $e instanceof ValidationException:
                    $errors = $e->validator->errors();
                    return response()->unprocessable('Parametros inválidos',$errors);

                case $e instanceof TokenExpiredException:
                case $e instanceof UnauthorizedHttpException:
                case $e instanceof TokenBlacklistedException:
                    return response()->unauthorized($e->getMessage());

                default:
                    return response()->json(['error' => $e->getMessage()], $statusCode);
            }
        });

    })->create();
