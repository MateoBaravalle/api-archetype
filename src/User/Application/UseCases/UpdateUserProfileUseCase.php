<?php

declare(strict_types=1);

namespace Src\User\Application\UseCases;

use Src\Auth\Domain\Entities\User;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;

class UpdateUserProfileUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Actualiza el perfil de un usuario
     *
     * @param int $userId ID del usuario
     * @param array $data Datos a actualizar: ['name', 'email']
     * @return User
     */
    public function execute(int $userId, array $data): User
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new \RuntimeException("Usuario con ID {$userId} no encontrado");
        }

        $user->updateProfile($data['name'], $data['email']);

        return $this->userRepository->update($user);
    }
}


