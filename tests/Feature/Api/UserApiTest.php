<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    #[Test]
    public function it_can_get_user_profile()
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/v1/users/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
            ]);
    }

    #[Test]
    public function it_can_update_user_profile()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/v1/users/profile', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', $updateData);
    }

    #[Test]
    public function it_validates_update_data()
    {
        $updateData = [
            'name' => 'AB', // Less than 3 characters
            'email' => 'invalid-email',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/v1/users/profile', $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    #[Test]
    public function it_can_change_password()
    {
        $passwordData = [
            'current_password' => 'password',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/v1/users/password', $passwordData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password updated successfully',
            ]);
    }

    #[Test]
    public function it_validates_password_change()
    {
        $passwordData = [
            'current_password' => 'wrongpassword',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/v1/users/password', $passwordData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }
}
