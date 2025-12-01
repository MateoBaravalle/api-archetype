<?php

declare(strict_types=1);

namespace Src\Auth\Application\UseCases;

use Src\Auth\Domain\Entities\User;
use Src\Auth\Domain\Events\UserRegistered;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;
use Src\Auth\Domain\Services\PasswordHasherInterface;

class RegisterUseCase
{
    private UserRepositoryInterface $userRepository;
    private PasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Registra un nuevo usuario
     *
     * @param array $data Datos del usuario: ['name', 'email', 'password']
     * @return array ['user' => User, 'event' => UserRegistered]
     */
    public function execute(array $data): array
    {
        // Crear usuario del dominio
        $user = User::create(
            $data['name'],
            $data['email'],
            $data['password']
        );

        // Hashear la contraseña antes de guardar
        $hashedPassword = $this->passwordHasher->hash($data['password']);

        // Guardar en el repositorio
        $savedUser = $this->userRepository->save($user);

        // Crear evento de dominio
        $event = new UserRegistered($savedUser);

        return [
            'user' => $savedUser,
            'event' => $event,
        ];
    }
}


