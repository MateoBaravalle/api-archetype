<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_user_can_register(): void
    {
        $userData = [
            'name' => 'Usuario Prueba',
            'email' => 'usuario@prueba.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/v1/auth', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'token',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'usuario@prueba.com',
            'name' => 'Usuario Prueba',
        ]);
    }

    #[Test]
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'usuario@prueba.com',
            'password' => bcrypt('Password123!'),
        ]);

        $loginData = [
            'email' => 'usuario@prueba.com',
            'password' => 'Password123!',
        ];

        $response = $this->postJson('/api/v1/auth', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'token',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    #[Test]
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/auth');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Session closed successfully',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    #[Test]
    public function test_user_can_get_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    #[Test]
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'usuario@prueba.com',
            'password' => bcrypt('Password123!'),
        ]);

        $loginData = [
            'email' => 'usuario@prueba.com',
            'password' => 'WrongPassword123!',
        ];

        $response = $this->postJson('/api/v1/auth', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function test_user_cannot_register_with_invalid_email_and_password(): void
    {
        $userData = [
            'name' => 'Usuario Prueba',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '123',
        ];

        $response = $this->postJson('/api/v1/auth', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    #[Test]
    public function test_user_cannot_register_with_invalid_name(): void
    {
        $userData = [
            'name' => 'AB', // Less than 3 characters
            'email' => 'valido@email.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/v1/auth', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function test_user_cannot_register_with_unconfirmed_password(): void
    {
        $userData = [
            'name' => 'Usuario Prueba',
            'email' => 'valido@email.com',
            'password' => 'Password123!',
            'password_confirmation' => 'OtraPassword123!',
        ];

        $response = $this->postJson('/api/v1/auth', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function test_token_has_correct_format(): void
    {
        $userData = [
            'name' => 'Usuario Prueba',
            'email' => 'usuario@prueba.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/v1/auth', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'token',
                ],
            ]);

        $token = $response->json('data.token');
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('|', $token);
    }

    #[Test]
    public function test_cannot_access_protected_routes_without_token(): void
    {
        $response = $this->getJson('/api/v1/auth');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated',
            ]);
    }

    #[Test]
    public function test_cannot_access_with_invalid_token(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/v1/auth');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated',
            ]);
    }

    #[Test]
    public function it_can_register_a_new_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/v1/auth', $userData);

        $this->assertApiResponse($response, 201);
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function it_can_login_an_existing_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->postJson('/api/v1/auth', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $this->assertApiResponse($response);
        $this->assertNotNull($response->json('data.token'));
    }

    #[Test]
    public function it_can_logout_a_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/auth');

        $this->assertApiResponse($response);
        $this->assertCount(0, $user->fresh()->tokens);
    }

    #[Test]
    public function it_validates_required_fields_on_registration()
    {
        $response = $this->postJson('/api/v1/auth', []);

        $this->assertApiError($response, 422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function it_validates_password_confirmation_on_registration()
    {
        $response = $this->postJson('/api/v1/auth', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $this->assertApiError($response, 422);
        $response->assertJsonValidationErrors(['password']);
    }
}
