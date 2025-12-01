<?php

declare(strict_types=1);

namespace Src\User\Application\UseCases;

use Src\Auth\Domain\Entities\User;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;

class UpdateUserPasswordUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Actualiza la contraseña de un usuario
     *
     * @param int $userId ID del usuario
     * @param string $newPassword Nueva contraseña en texto plano
     * @return bool
     */
    public function execute(int $userId, string $newPassword): bool
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new \RuntimeException("Usuario con ID {$userId} no encontrado");
        }

        $user->changePassword($newPassword);
        $this->userRepository->update($user);

        return true;
    }
}


