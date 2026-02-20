<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de Monitoreo de Performance
 *
 * Registra métricas de performance de cada request HTTP:
 * - Tiempo de ejecución
 * - Uso de memoria
 * - Información del request
 *
 * Se configura para alertar sobre requests lentos (> 1000ms)
 * permitiendo identificar cuellos de botella en la aplicación.
 */
class LogPerformance
{
    /**
     * Umbral de tiempo en milisegundos para considerar un request como lento
     */
    private const SLOW_REQUEST_THRESHOLD = 1000;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Capturar tiempo inicial y memoria
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Procesar el request
        $response = $next($request);

        // Calcular métricas de performance
        $duration = (microtime(true) - $startTime) * 1000; // Convertir a milisegundos
        $memoryUsed = (memory_get_usage() - $startMemory) / 1024 / 1024; // Convertir a MB

        // Preparar contexto de log
        $context = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'duration_ms' => round($duration, 2),
            'memory_mb' => round($memoryUsed, 2),
            'status_code' => $response->getStatusCode(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        // Añadir ID de usuario si está autenticado
        if (auth()->check()) {
            $context['user_id'] = auth()->id();
            $context['user_email'] = auth()->user()?->email;
        }

        // Loggear como warning si el request es lento
        if ($duration > self::SLOW_REQUEST_THRESHOLD) {
            Log::warning('Slow request detected', $context);
        } else {
            // Loggear como info para requests normales (puede desactivarse en prod)
            if (config('app.debug')) {
                Log::info('Request processed', $context);
            }
        }

        // Añadir headers de respuesta con métricas (útil para debug)
        if (config('app.debug')) {
            $response->headers->set('X-Response-Time', round($duration, 2) . 'ms');
            $response->headers->set('X-Memory-Usage', round($memoryUsed, 2) . 'MB');
        }

        return $response;
    }
}
