<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * User authentication (login or registration)
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
     * User logout (revoke token)
     */
    public function destroy(Request $request): JsonResponse
    {
        $this->authService->revokeAllTokens($request->user());

        return $this->successResponse(null, 'Session closed successfully');
    }

    /**
     * Get the authenticated user
     */
    public function show(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'User obtained successfully');
    }
}
