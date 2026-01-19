<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use WithFaker;

    /**
     * Base configuration for all tests
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable email sending by default
        Mail::fake();

        // Disable queues by default
        Queue::fake();
    }

    /**
     * Helper to create an authenticated user
     */
    protected function createAuthenticatedUser($attributes = [])
    {
        $user = \App\Models\User::factory()->create($attributes);
        $token = $user->createToken('test-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Accept' => 'application/json',
            ],
        ];
    }

    /**
     * Helper to verify the API response structure
     */
    protected function assertApiResponse($response, $status = 200)
    {
        $response->assertStatus($status)
            ->assertJsonStructure([
                'success',
                'message',
            ]);

        // If the response includes the 'data' key (even if it's null), we verify it
        // If it doesn't include it (case of returning null in successResponse), we skip the data structure check
        if (array_key_exists('data', $response->json())) {
            $response->assertJsonStructure(['data']);
        }
    }

    /**
     * Helper to verify the API error response structure
     */
    protected function assertApiError($response, $status = 400)
    {
        $response->assertStatus($status)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }
}
