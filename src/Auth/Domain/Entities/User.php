<?php

declare(strict_types=1);

namespace Src\Auth\Domain\Entities;

use DateTimeImmutable;
use Src\Auth\Domain\ValueObjects\Email;
use Src\Auth\Domain\ValueObjects\Password;

class User
{
    private ?int $id;
    private string $name;
    private Email $email;
    private Password $password;
    private ?DateTimeImmutable $emailVerifiedAt;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        string $name,
        Email $email,
        Password $password,
        ?DateTimeImmutable $emailVerifiedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        string $name,
        string $email,
        string $plainPassword
    ): self {
        return new self(
            null,
            $name,
            new Email($email),
            Password::fromPlainText($plainPassword),
            null,
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );
    }

    public function updateProfile(string $name, string $email): void
    {
        $this->name = $name;
        $this->email = new Email($email);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changePassword(string $plainPassword): void
    {
        $this->password = Password::fromPlainText($plainPassword);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function verifyEmail(): void
    {
        $this->emailVerifiedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): Password
    {
        return $this->password;
    }

    public function emailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email->value(),
            'email_verified_at' => $this->emailVerifiedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}


