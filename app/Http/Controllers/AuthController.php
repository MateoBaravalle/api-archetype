<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Models\User; // Mantengo este import por si acaso, aunque ya no se usa explícitamente en store, el replace lo pedía.
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Autenticación de usuario (login o registro)
     */
    public function store(AuthRequest $request): JsonResponse
    {
        $result = $this->authService->authenticateOrRegister($request->validated());

        return $this->successResponse(
            $result['user'],
            $result['message'],
            $result['status']
        );
    }

    /**
     * Logout del usuario (revocar token)
     */
    public function destroy(Request $request): JsonResponse
    {
        $this->authService->revokeAllTokens($request->user());

        return $this->successResponse(null, 'Sesión cerrada exitosamente');
    }

    /**
     * Obtener el usuario autenticado
     */
    public function show(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'Usuario obtenido exitosamente');
    }
}
