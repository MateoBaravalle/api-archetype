<?php

declare(strict_types=1);

namespace Src\Auth\Domain\Services;

interface PasswordHasherInterface
{
    /**
     * Hashea una contraseña en texto plano
     */
    public function hash(string $plainPassword): string;

    /**
     * Verifica si una contraseña coincide con su hash
     */
    public function verify(string $plainPassword, string $hashedPassword): bool;
}


