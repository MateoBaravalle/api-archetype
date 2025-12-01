<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Repositories;

use DateTimeImmutable;
use Src\Auth\Domain\Entities\User;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;
use Src\Auth\Domain\Services\PasswordHasherInterface;
use Src\Auth\Domain\ValueObjects\Email;
use Src\Auth\Domain\ValueObjects\Password;
use Src\Auth\Infrastructure\Persistence\EloquentUserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    private EloquentUserModel $model;
    private PasswordHasherInterface $passwordHasher;

    public function __construct(EloquentUserModel $model, PasswordHasherInterface $passwordHasher)
    {
        $this->model = $model;
        $this->passwordHasher = $passwordHasher;
    }

    public function findById(int $id): ?User
    {
        $eloquentUser = $this->model->find($id);

        if (! $eloquentUser) {
            return null;
        }

        return $this->toDomain($eloquentUser);
    }

    public function findByEmail(string $email): ?User
    {
        $eloquentUser = $this->model->where('email', $email)->first();

        if (! $eloquentUser) {
            return null;
        }

        return $this->toDomain($eloquentUser);
    }

    public function save(User $user): User
    {
        $hashedPassword = $this->passwordHasher->hash($user->password()->value());

        $eloquentUser = $this->model->create([
            'name' => $user->name(),
            'email' => $user->email()->value(),
            'password' => $hashedPassword,
        ]);

        return $this->toDomain($eloquentUser);
    }

    public function update(User $user): User
    {
        $eloquentUser = $this->model->find($user->id());

        if (! $eloquentUser) {
            throw new \RuntimeException("User with ID {$user->id()} not found");
        }

        $data = [
            'name' => $user->name(),
            'email' => $user->email()->value(),
        ];

        // Solo actualizar password si se está cambiando
        if ($user->password()->value() !== $eloquentUser->password) {
            $data['password'] = $this->passwordHasher->hash($user->password()->value());
        }

        $eloquentUser->update($data);

        return $this->toDomain($eloquentUser->fresh());
    }

    /**
     * Convierte un modelo de Eloquent a entidad de dominio
     */
    private function toDomain(EloquentUserModel $eloquentUser): User
    {
        $emailVerifiedAt = null;
        if ($eloquentUser->email_verified_at) {
            $emailVerifiedAt = new DateTimeImmutable($eloquentUser->email_verified_at->format('Y-m-d H:i:s'));
        }

        return new User(
            $eloquentUser->id,
            $eloquentUser->name,
            new Email($eloquentUser->email),
            Password::fromHash($eloquentUser->password),
            $emailVerifiedAt,
            new DateTimeImmutable($eloquentUser->created_at->format('Y-m-d H:i:s')),
            new DateTimeImmutable($eloquentUser->updated_at->format('Y-m-d H:i:s'))
        );
    }
}


