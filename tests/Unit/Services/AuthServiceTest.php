<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Events\UserRegistered;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_it_registers_new_user_if_email_does_not_exist(): void
    {
        Event::fake();

        $credentials = [
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'name' => 'New User'
        ];

        $result = $this->authService->authenticateOrRegister($credentials);

        $this->assertEquals(201, $result['status']);
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
        Event::assertDispatched(UserRegistered::class);
    }

    public function test_it_authenticates_existing_user_with_correct_password(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => Hash::make('password123')
        ]);

        $credentials = [
            'email' => 'existing@example.com',
            'password' => 'password123'
        ];

        $result = $this->authService->authenticateOrRegister($credentials);

        $this->assertEquals(200, $result['status']);
        $this->assertEquals($user->id, $result['user']->id);
    }

    public function test_it_throws_validation_exception_for_existing_user_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
            'password' => Hash::make('password123')
        ]);

        $credentials = [
            'email' => 'existing@example.com',
            'password' => 'wrongpassword'
        ];

        $this->expectException(ValidationException::class);
        $this->authService->authenticateOrRegister($credentials);
    }
}
