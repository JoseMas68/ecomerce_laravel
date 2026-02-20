<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de Control de Acceso a Laravel Telescope
 *
 * Protege el acceso al dashboard de Telescope:
 * - En ambiente local: acceso libre
 * - En producción: solo para usuarios administradores
 *
 * Esto es crítico para la seguridad, ya que Telescope expone
 * información sensible de la aplicación.
 */
class TelescopeAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Permitir acceso en ambiente local
        if (app()->environment('local')) {
            return $next($request);
        }

        // En producción, verificar que el usuario esté autenticado y sea admin
        if (app()->environment('production')) {
            // Verificar autenticación
            if (!Auth::check()) {
                abort(403, 'Access denied. Authentication required.');
            }

            // Verificar rol de administrador
            // Nota: Ajustar según el sistema de roles implementado
            $user = Auth::user();

            if (
                !$user->hasRole('admin') &&
                !$user->hasRole('super_admin') &&
                $user->email !== config('telescope.admin_email')
            ) {
                abort(403, 'Access denied. Administrator privileges required.');
            }
        }

        return $next($request);
    }
}
