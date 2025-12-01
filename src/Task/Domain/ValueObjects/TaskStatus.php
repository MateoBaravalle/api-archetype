<?php

declare(strict_types=1);

namespace Src\Task\Domain\ValueObjects;

use InvalidArgumentException;

final class TaskStatus
{
    private const VALID_STATUSES = ['pending', 'in_progress', 'completed', 'cancelled'];

    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate(string $value): void
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid status "%s". Valid statuses are: %s',
                    $value,
                    implode(', ', self::VALID_STATUSES)
                )
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(TaskStatus $other): bool
    {
        return $this->value === $other->value;
    }

    public function isPending(): bool
    {
        return $this->value === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->value === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->value === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->value === 'cancelled';
    }

    public function __toString(): string
    {
        return $this->value;
    }
}


