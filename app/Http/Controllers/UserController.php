<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\AuthResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Obtener el perfil del usuario autenticado
     */
    public function profile(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'Perfil obtenido exitosamente');
    }

    /**
     * Actualizar el perfil del usuario
     */
    public function updateProfile(UserRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->successResponse($user, 'Perfil actualizado exitosamente');
    }

    /**
     * Actualizar la contraseña del usuario
     */
    public function updatePassword(UserRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return $this->successResponse(null, 'Contraseña actualizada exitosamente');
    }
}
