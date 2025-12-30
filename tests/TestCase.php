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
     * Configuración base para todos los tests
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Desactivar envío de emails por defecto
        Mail::fake();

        // Desactivar colas por defecto
        Queue::fake();
    }

    /**
     * Helper para crear un usuario autenticado
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
     * Helper para verificar la estructura de respuesta API
     */
    protected function assertApiResponse($response, $status = 200)
    {
        $response->assertStatus($status)
            ->assertJsonStructure([
                'success',
                'message',
            ]);

        // Si la respuesta incluye la clave 'data' (incluso si es null), la verificamos
        // Si no la incluye (caso de retorno null en successResponse), omitimos el chequeo de estructura de data
        if (array_key_exists('data', $response->json())) {
            $response->assertJsonStructure(['data']);
        }
    }

    /**
     * Helper para verificar la estructura de respuesta de error API
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
