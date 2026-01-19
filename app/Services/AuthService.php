<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Authenticates or registers a user based on whether they exist or not
     *
     * @return array{user: User, message: string, status: int}
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticateOrRegister(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        // 1. Registration Flow
        if (! $user) {
            return $this->registerUser($credentials);
        }

        // 2. Login Flow
        if (! Hash::check($credentials['password'], $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->token = $this->createApiToken($user, 'auth-token');

        return [
            'user' => $user,
            'message' => 'User authenticated successfully',
            'status' => 200,
        ];
    }

    /**
     * Creates a new user and their initial token
     */
    protected function registerUser(array $userData): array
    {
        return DB::transaction(function () use ($userData) {
            $user = $this->createUser($userData);

            // Trigger the registration event Maryland
            event(new \App\Events\UserRegistered($user));

            $user->token = $this->createApiToken($user, 'auth-token');

            return [
                'user' => $user,
                'message' => 'User registered successfully',
                'status' => 201,
            ];
        });
    }

    /**
     * Creates a new user in the database
     */
    protected function createUser(array $userData): User
    {
        return User::create([
            'name' => $userData['name'] ?? explode('@', $userData['email'])[0],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);
    }

    /**
     * Revokes all user tokens
     */
    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Creates a new API token for the user
     */
    public function createApiToken(User $user, string $tokenName): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }
}
