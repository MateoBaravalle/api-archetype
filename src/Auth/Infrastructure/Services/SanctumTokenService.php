<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Services;

use Src\Auth\Domain\Entities\User;
use Src\Auth\Domain\Services\PasswordHasherInterface;
use Src\Auth\Domain\Services\TokenServiceInterface;
use Src\Auth\Infrastructure\Persistence\EloquentUserModel;

class SanctumTokenService implements TokenServiceInterface
{
    private PasswordHasherInterface $passwordHasher;

    public function __construct(PasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function createToken(User $user, string $tokenName): string
    {
        // Obtener el modelo Eloquent correspondiente
        $eloquentUser = EloquentUserModel::find($user->id());

        if (! $eloquentUser) {
            throw new \RuntimeException("User with ID {$user->id()} not found");
        }

        return $eloquentUser->createToken($tokenName)->plainTextToken;
    }

    public function revokeAllTokens(User $user): void
    {
        $eloquentUser = EloquentUserModel::find($user->id());

        if (! $eloquentUser) {
            throw new \RuntimeException("User with ID {$user->id()} not found");
        }

        $eloquentUser->tokens()->delete();
    }

    public function validateCredentials(string $email, string $plainPassword): bool
    {
        $eloquentUser = EloquentUserModel::where('email', $email)->first();

        if (! $eloquentUser) {
            return false;
        }

        return $this->passwordHasher->verify($plainPassword, $eloquentUser->password);
    }
}


