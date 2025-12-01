<?php

declare(strict_types=1);

namespace Src\Auth\Domain\ValueObjects;

use InvalidArgumentException;

final class Email
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = strtolower(trim($value));
    }

    private function validate(string $value): void
    {
        $email = trim($value);

        if (empty($email)) {
            throw new InvalidArgumentException('El email no puede estar vacío');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("El email '{$email}' no es válido");
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}


