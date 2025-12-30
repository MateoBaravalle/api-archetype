<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Autentica o registra un usuario basado en si existe o no
     *
     * @return array{user: User, message: string, status: int}
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticateOrRegister(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        // 1. Flujo de Registro
        if (! $user) {
            return $this->registerUser($credentials);
        }

        // 2. Flujo de Login
        if (! Hash::check($credentials['password'], $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $user->token = $this->createApiToken($user, 'auth-token');

        return [
            'user' => $user,
            'message' => 'Usuario autenticado exitosamente',
            'status' => 200,
        ];
    }

    /**
     * Crea un nuevo usuario y su token inicial
     */
    protected function registerUser(array $userData): array
    {
        return DB::transaction(function () use ($userData) {
            $user = $this->createUser($userData);

            // Disparamos el evento de registro
            event(new \App\Events\UserRegistered($user));

            $user->token = $this->createApiToken($user, 'auth-token');

            return [
                'user' => $user,
                'message' => 'Usuario registrado exitosamente',
                'status' => 201,
            ];
        });
    }

    /**
     * Crea un nuevo usuario en base de datos
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
     * Revoca todos los tokens del usuario
     */
    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Crea un nuevo token API para el usuario
     */
    public function createApiToken(User $user, string $tokenName): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }
}
