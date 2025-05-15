<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\AuthResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Obtener el perfil del usuario autenticado
     */
    public function profile(Request $request)
    {
        try {
            return $this->successResponse(new AuthResource($request->user()), 'Perfil obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Actualizar el perfil del usuario
     */
    public function updateProfile(UserRequest $request)
    {
        try {
            $user = $request->user();
            $user->update($request->validated());

            return $this->successResponse(new AuthResource($user), 'Perfil actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Actualizar la contraseña del usuario
     */
    public function updatePassword(UserRequest $request)
    {
        try {
            $user = $request->user();
            $user->update([
                'password' => Hash::make($request->validated('password')),
            ]);

            return $this->successResponse(null, 'Contraseña actualizada exitosamente');
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }
}
