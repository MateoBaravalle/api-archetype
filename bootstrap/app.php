<?php

declare(strict_types=1);

use App\Http\Middleware\ApiRateLimiter;
use App\Http\Middleware\ValidateApiHeaders;
use App\Traits\ApiResponseFormatter;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api([
            ValidateApiHeaders::class,
            ApiRateLimiter::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $data = ApiResponseFormatter::parseExceptionPayload($e);

                if ($data['status'] >= 500) {
                    Log::error('Error en la aplicación', [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $data['message'],
                    'errors' => $data['errors'],
                ], $data['status']);
            }
        });
    })->create();
