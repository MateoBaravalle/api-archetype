<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $auth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth = $this->createAuthenticatedUser();
    }

    public function test_can_list_tasks(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->withHeaders($this->auth['headers'])
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'status',
                            'priority',
                            'due_date',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'meta' => [
                        'pagination' => [
                            'total',
                            'count',
                            'per_page',
                            'current_page',
                            'total_pages',
                            'has_more_pages'
                        ]
                    ]
                ]
            ]);
    }

    public function test_can_create_task_with_valid_data(): void
    {
        $taskData = [
            'title' => 'Nueva Tarea',
            'description' => 'Descripción de la tarea',
            'status' => 'pendiente'
        ];

        $response = $this->withHeaders($this->auth['headers'])
            ->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'due_date',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('tasks', $taskData);
    }

    public function test_cannot_create_task_with_invalid_data(): void
    {
        $response = $this->withHeaders($this->auth['headers'])
            ->postJson('/api/v1/tasks', [
                'title' => '',
                'status' => 'invalid_status'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status']);
    }

    public function test_can_show_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->withHeaders($this->auth['headers'])
            ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'due_date',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_cannot_show_nonexistent_task(): void
    {
        $response = $this->withHeaders($this->auth['headers'])
            ->getJson('/api/v1/tasks/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Recurso no encontrado'
            ]);
    }

    public function test_can_update_task(): void
    {
        $task = Task::factory()->create();
        $updateData = [
            'title' => 'Tarea Actualizada',
            'status' => 'completada'
        ];

        $response = $this->withHeaders($this->auth['headers'])
            ->putJson("/api/v1/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'due_date',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('tasks', $updateData);
    }

    public function test_can_delete_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->withHeaders($this->auth['headers'])
            ->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Tarea eliminada correctamente'
            ]);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
