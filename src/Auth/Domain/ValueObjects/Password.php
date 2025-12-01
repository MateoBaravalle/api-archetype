<?php

declare(strict_types=1);

namespace Src\Auth\Domain\ValueObjects;

use InvalidArgumentException;

final class Password
{
    private const MIN_LENGTH = 8;

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Crea una contraseña desde texto plano con validación
     */
    public static function fromPlainText(string $plainPassword): self
    {
        self::validate($plainPassword);

        return new self($plainPassword);
    }

    /**
     * Crea una contraseña desde un hash (ya hasheada)
     */
    public static function fromHash(string $hashedPassword): self
    {
        return new self($hashedPassword);
    }

    private static function validate(string $password): void
    {
        if (empty($password)) {
            throw new InvalidArgumentException('La contraseña no puede estar vacía');
        }

        if (strlen($password) < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('La contraseña debe tener al menos %d caracteres', self::MIN_LENGTH)
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}


