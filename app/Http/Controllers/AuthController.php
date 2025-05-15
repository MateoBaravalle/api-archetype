<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use App\Http\Requests\AuthRequest;
use App\Http\Resources\AuthResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Autenticación de usuario (login o registro)
     */
    public function store(AuthRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            // Si el usuario no existe, lo registramos
            if (! $user) {
                $user = $this->authService->createUser($request->validated());

                // Disparamos el evento de registro
                event(new UserRegistered($user));

                $user->token = $this->authService->createApiToken($user, 'auth-token');

                return $this->successResponse(new AuthResource($user), 'Usuario registrado exitosamente', 201);
            }

            // Si el usuario existe, verificamos la contraseña
            if (! $this->authService->validateCredentials($request->email, $request->password)) {
                return $this->errorResponse('Las credenciales proporcionadas son incorrectas.', 422, ['email' => ['Las credenciales proporcionadas son incorrectas.']]);
            }

            $user->token = $this->authService->createApiToken($user, 'auth-token');

            return $this->successResponse(new AuthResource($user), 'Usuario autenticado exitosamente');
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Logout del usuario (revocar token)
     */
    public function destroy(Request $request)
    {
        try {
            $this->authService->revokeAllTokens($request->user());

            return $this->successResponse(null, 'Sesión cerrada exitosamente');
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Obtener el usuario autenticado
     */
    public function show(Request $request)
    {
        try {
            return $this->successResponse(new AuthResource($request->user()), 'Usuario obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }
}
