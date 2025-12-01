<?php

declare(strict_types=1);

namespace Src\User\Infrastructure\Http\Controllers;

use Src\User\Infrastructure\Http\Requests\UserRequest;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Src\Shared\Infrastructure\ApiResponseFormatter;
use Src\User\Application\UseCases\GetUserProfileUseCase;
use Src\User\Application\UseCases\UpdateUserPasswordUseCase;
use Src\User\Application\UseCases\UpdateUserProfileUseCase;

class UserController extends BaseController
{
    use ApiResponseFormatter;
    use AuthorizesRequests;
    use ValidatesRequests;

    private GetUserProfileUseCase $getUserProfileUseCase;
    private UpdateUserProfileUseCase $updateUserProfileUseCase;
    private UpdateUserPasswordUseCase $updateUserPasswordUseCase;

    public function __construct(
        GetUserProfileUseCase $getUserProfileUseCase,
        UpdateUserProfileUseCase $updateUserProfileUseCase,
        UpdateUserPasswordUseCase $updateUserPasswordUseCase
    ) {
        $this->getUserProfileUseCase = $getUserProfileUseCase;
        $this->updateUserProfileUseCase = $updateUserProfileUseCase;
        $this->updateUserPasswordUseCase = $updateUserPasswordUseCase;
    }

    /**
     * Obtener el perfil del usuario autenticado
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $user = $this->getUserProfileUseCase->execute($userId);

            if (! $user) {
                return $this->errorResponse('Usuario no encontrado', 404);
            }

            return $this->successResponse($user->toArray(), 'Perfil obtenido exitosamente');
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Actualizar el perfil del usuario
     */
    public function updateProfile(UserRequest $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $user = $this->updateUserProfileUseCase->execute($userId, $request->validated());

            return $this->successResponse($user->toArray(), 'Perfil actualizado exitosamente');
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Actualizar la contraseña del usuario
     */
    public function updatePassword(UserRequest $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $this->updateUserPasswordUseCase->execute($userId, $request->validated('password'));

            return $this->successResponse(null, 'Contraseña actualizada exitosamente');
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
}

