<?php

declare(strict_types=1);

namespace Src\User\Application\UseCases;

use Src\Auth\Domain\Entities\User;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;

class GetUserProfileUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Obtiene el perfil de un usuario
     */
    public function execute(int $userId): ?User
    {
        return $this->userRepository->findById($userId);
    }
}


