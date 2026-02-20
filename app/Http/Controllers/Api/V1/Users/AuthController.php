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
        $user = \App\Models\User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // Crear token con habilidades básicas
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'user' => UserResource::make($user),
                'token' => $token,
                'token_type' => 'Bearer',
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
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $user = Auth::user();
        if (!$user instanceof \App\Models\User) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el usuario autenticado',
            ], 500);
        }

        // Revocar tokens anteriores para mantener solo una sesión activa
        $user->tokens()->delete();

        // Crear nuevo token
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'data' => [
                'user' => UserResource::make($user),
                'token' => $token,
                'token_type' => 'Bearer',
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
