<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService;
    }

    #[Test]
    public function it_can_validate_user_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $result = $this->authService->validateCredentials('test@example.com', 'password123');
        $this->assertTrue($result);

        $result = $this->authService->validateCredentials('test@example.com', 'wrongpassword');
        $this->assertFalse($result);
    }

    #[Test]
    public function it_can_create_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $user = $this->authService->createUser($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    #[Test]
    public function it_can_revoke_all_tokens()
    {
        $user = User::factory()->create();
        $user->createToken('test-token-1');
        $user->createToken('test-token-2');

        $this->authService->revokeAllTokens($user);

        $this->assertCount(0, $user->tokens);
    }

    #[Test]
    public function it_can_create_api_token()
    {
        $user = User::factory()->create();
        $token = $this->authService->createApiToken($user, 'test-token');

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('|', $token);
        $this->assertCount(1, $user->tokens);
    }
}
