<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Http\Controllers;

use Src\Auth\Infrastructure\Http\Requests\AuthRequest;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use InvalidArgumentException;
use Src\Auth\Application\UseCases\GetAuthenticatedUserUseCase;
use Src\Auth\Application\UseCases\LoginUseCase;
use Src\Auth\Application\UseCases\LogoutUseCase;
use Src\Auth\Application\UseCases\RegisterUseCase;
use Src\Auth\Domain\Services\TokenServiceInterface;
use Src\Shared\Infrastructure\ApiResponseFormatter;

class AuthController extends BaseController
{
    use ApiResponseFormatter;
    use AuthorizesRequests;
    use ValidatesRequests;

    private RegisterUseCase $registerUseCase;
    private LoginUseCase $loginUseCase;
    private LogoutUseCase $logoutUseCase;
    private GetAuthenticatedUserUseCase $getAuthenticatedUserUseCase;
    private TokenServiceInterface $tokenService;

    public function __construct(
        RegisterUseCase $registerUseCase,
        LoginUseCase $loginUseCase,
        LogoutUseCase $logoutUseCase,
        GetAuthenticatedUserUseCase $getAuthenticatedUserUseCase,
        TokenServiceInterface $tokenService
    ) {
        $this->registerUseCase = $registerUseCase;
        $this->loginUseCase = $loginUseCase;
        $this->logoutUseCase = $logoutUseCase;
        $this->getAuthenticatedUserUseCase = $getAuthenticatedUserUseCase;
        $this->tokenService = $tokenService;
    }

    /**
     * Autenticación de usuario (login o registro)
     */
    public function store(AuthRequest $request): JsonResponse
    {
        try {
            // Intentar login primero
            try {
                $user = $this->loginUseCase->execute($request->email, $request->password);
                $token = $this->tokenService->createToken($user, 'auth-token');

                $userData = $user->toArray();
                $userData['token'] = $token;

                return $this->successResponse($userData, 'Usuario autenticado exitosamente');
            } catch (InvalidArgumentException $e) {
                // Si falla el login, intentar registro
                $result = $this->registerUseCase->execute($request->validated());
                $user = $result['user'];
                $event = $result['event'];

                // Disparar evento de Laravel
                event(new \App\Events\UserRegistered(
                    \App\Models\User::find($user->id())
                ));

                $token = $this->tokenService->createToken($user, 'auth-token');

                $userData = $user->toArray();
                $userData['token'] = $token;

                return $this->successResponse($userData, 'Usuario registrado exitosamente', 201);
            }
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Logout del usuario (revocar token)
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $user = $this->getAuthenticatedUserUseCase->execute($userId);

            if (! $user) {
                return $this->errorResponse('Usuario no encontrado', 404);
            }

            $this->logoutUseCase->execute($user);

            return $this->successResponse(null, 'Sesión cerrada exitosamente');
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Obtener el usuario autenticado
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $user = $this->getAuthenticatedUserUseCase->execute($userId);

            if (! $user) {
                return $this->errorResponse('Usuario no encontrado', 404);
            }

            return $this->successResponse($user->toArray(), 'Usuario obtenido exitosamente');
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
}

