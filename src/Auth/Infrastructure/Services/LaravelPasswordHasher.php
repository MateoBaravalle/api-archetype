<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Services;

use Illuminate\Support\Facades\Hash;
use Src\Auth\Domain\Services\PasswordHasherInterface;

class LaravelPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        return Hash::make($plainPassword);
    }

    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        return Hash::check($plainPassword, $hashedPassword);
    }
}


