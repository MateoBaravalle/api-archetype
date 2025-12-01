<?php

declare(strict_types=1);

namespace Src\Auth\Application\UseCases;

use Src\Auth\Domain\Entities\User;
use Src\Auth\Domain\Services\TokenServiceInterface;

class LogoutUseCase
{
    private TokenServiceInterface $tokenService;

    public function __construct(TokenServiceInterface $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Cierra sesión revocando todos los tokens del usuario
     */
    public function execute(User $user): void
    {
        $this->tokenService->revokeAllTokens($user);
    }
}


