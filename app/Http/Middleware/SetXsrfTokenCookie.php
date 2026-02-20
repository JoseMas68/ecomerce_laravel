<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetXsrfTokenCookie
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Establecer siempre la cookie XSRF-TOKEN para mantener sincronizado a Inertia.js
        $response->headers->setCookie(
            new \Symfony\Component\HttpFoundation\Cookie(
                'XSRF-TOKEN',
                csrf_token(),
                0,
                '/',
                null,
                $request->secure(),
                false,
                false,
                \Symfony\Component\HttpFoundation\Cookie::SAMESITE_LAX
            )
        );

        return $response;
    }
}
