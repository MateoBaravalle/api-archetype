<?php

declare(strict_types=1);

namespace Src\Auth\Application\UseCases;

use InvalidArgumentException;
use Src\Auth\Domain\Entities\User;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;
use Src\Auth\Domain\Services\PasswordHasherInterface;

class LoginUseCase
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
     * Autentica un usuario con sus credenciales
     *
     * @param string $email Email del usuario
     * @param string $password Contraseña en texto plano
     * @return User Usuario autenticado
     * @throws InvalidArgumentException Si las credenciales son inválidas
     */
    public function execute(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user) {
            throw new InvalidArgumentException('Las credenciales proporcionadas son incorrectas.');
        }

        if (! $this->passwordHasher->verify($password, $user->password()->value())) {
            throw new InvalidArgumentException('Las credenciales proporcionadas son incorrectas.');
        }

        return $user;
    }
}


