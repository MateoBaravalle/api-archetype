<?php

declare(strict_types=1);

namespace Src\Task\Domain\ValueObjects;

use InvalidArgumentException;

final class TaskPriority
{
    private const MIN_PRIORITY = 1;
    private const MAX_PRIORITY = 5;

    private int $value;

    public function __construct(int $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate(int $value): void
    {
        if ($value < self::MIN_PRIORITY || $value > self::MAX_PRIORITY) {
            throw new InvalidArgumentException(
                sprintf(
                    'Priority must be between %d and %d, got %d',
                    self::MIN_PRIORITY,
                    self::MAX_PRIORITY,
                    $value
                )
            );
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(TaskPriority $other): bool
    {
        return $this->value === $other->value;
    }

    public function isHigherThan(TaskPriority $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLowerThan(TaskPriority $other): bool
    {
        return $this->value < $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}


