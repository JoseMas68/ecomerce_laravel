<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

final class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario en el sistema.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Rate limiting: 5 registros por minuto por IP
        if (RateLimiter::tooManyAttempts('register:' . $request->ip(), 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Demasiados intentos de registro. Por favor intenta más tarde.',
            ], 429);
        }
        
        RateLimiter::hit('register:' . $request->ip(), 60);

        $user = \App\Models\User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // Crear token con habilidades básicas - EXPIRACIÓN A 30 DÍAS
        $token = $user->createToken('auth-token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'user' => UserResource::make($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(30)->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Inicia sesión de un usuario existente.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Rate limiting: 10 intentos por minuto por IP
        if (RateLimiter::tooManyAttempts('login:' . $request->ip(), 10)) {
            return response()->json([
                'success' => false,
                'message' => 'Demasiados intentos de inicio de sesión. Por favor intenta más tarde.',
            ], 429);
        }

        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                RateLimiter::hit('login:' . $request->ip(), 60);
                throw ValidationException::withMessages([
                    'email' => ['Las credenciales proporcionadas son incorrectas.'],
                ]);
            }
        } catch (Throwable $e) {
            RateLimiter::hit('login:' . $request->ip(), 60);
            throw $e;
        }

        RateLimiter::clear('login:' . $request->ip());

        $user = Auth::user();
        if (!$user instanceof \App\Models\User) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el usuario autenticado',
            ], 500);
        }

        // Revocar tokens anteriores para mantener solo una sesión activa
        $user->tokens()->delete();

        // Crear nuevo token con expiración de 30 días
        $token = $user->createToken('auth-token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'data' => [
                'user' => UserResource::make($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(30)->toIso8601String(),
            ],
        ]);
    }

    /**
     * Cierra la sesión del usuario actual y revoca su token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof \App\Models\User) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo identificar al usuario',
            ], 401);
        }

        // Revocar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cierre de sesión exitoso',
        ]);
    }

    /**
     * Revoca todos los tokens del usuario autenticado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof \App\Models\User) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo identificar al usuario',
            ], 401);
        }

        // Revocar todos los tokens del usuario
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Todos los tokens han sido revocados exitosamente',
        ]);
    }

    /**
     * Obtiene la lista de tokens activos del usuario.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function tokens(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof \App\Models\User) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo identificar al usuario',
            ], 401);
        }

        $tokens = $user->tokens->map(function ($token) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'created_at' => $token->created_at->toIso8601String(),
                'expires_at' => $token->expires_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'tokens' => $tokens,
            ],
        ]);
    }

    /**
     * Inicia el proceso de recuperación de contraseña.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Recuperación de contraseña no implementada',
        ], 501);
    }

    /**
     * Restablece la contraseña del usuario.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Restablecimiento de contraseña no implementado',
        ], 501);
    }
}
