<?php

declare(strict_types=1);

namespace Src\Auth\Application\UseCases;

use Src\Auth\Domain\Entities\User;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;

class GetAuthenticatedUserUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Obtiene el usuario autenticado por su ID
     */
    public function execute(int $userId): ?User
    {
        return $this->userRepository->findById($userId);
    }
}


