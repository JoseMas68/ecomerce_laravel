<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\SetXsrfTokenCookie::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
        ]);

        // Registrar middleware de monitoreo de performance
        // Aplicar a todos los requests (web y api)
        $middleware->append([
            \App\Http\Middleware\LogPerformance::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Registrar manejo de excepciones para monitoreo y tracking
        $exceptions->reportable(function (\Throwable $e) {
            // Determinar si la excepción debe ser reportada
            $dontReport = [
                \Illuminate\Auth\AuthenticationException::class,
                \Illuminate\Validation\ValidationException::class,
                \Symfony\Component\HttpKernel\Exception\HttpException::class,
            ];

            foreach ($dontReport as $type) {
                if ($e instanceof $type) {
                    return;
                }
            }

            // En producción, loggear errores críticos con contexto completo
            if (app()->environment('production')) {
                \Illuminate\Support\Facades\Log::error('Critical error detected', [
                    'message' => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'user_id' => auth()->id(),
                    'user_email' => auth()->user()?->email,
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toIso8601String(),
                ]);
            }

            // En desarrollo, loggear con menos detalle para no saturar
            if (app()->environment('local')) {
                \Illuminate\Support\Facades\Log::debug('Exception occurred', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        });
    })->create();
