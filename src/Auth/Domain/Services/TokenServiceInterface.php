<?php

declare(strict_types=1);

namespace Src\Auth\Domain\Services;

use Src\Auth\Domain\Entities\User;

interface TokenServiceInterface
{
    /**
     * Crea un nuevo token para el usuario
     */
    public function createToken(User $user, string $tokenName): string;

    /**
     * Revoca todos los tokens del usuario
     */
    public function revokeAllTokens(User $user): void;

    /**
     * Valida las credenciales del usuario
     */
    public function validateCredentials(string $email, string $plainPassword): bool;
}


