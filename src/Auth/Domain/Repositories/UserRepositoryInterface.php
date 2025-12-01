<?php

declare(strict_types=1);

namespace Src\Auth\Domain\Repositories;

use Src\Auth\Domain\Entities\User;

interface UserRepositoryInterface
{
    /**
     * Encuentra un usuario por su ID
     */
    public function findById(int $id): ?User;

    /**
     * Encuentra un usuario por su email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Guarda un nuevo usuario
     */
    public function save(User $user): User;

    /**
     * Actualiza un usuario existente
     */
    public function update(User $user): User;
}


