<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
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
        // Crear tareas asociadas al usuario actual para que pueda verlas si la policy viewAny filtra (aunque viewAny actual retorna true)
        Task::factory()->count(3)->create(['created_by' => $this->auth['user']->id]);

        $response = $this->withHeaders($this->auth['headers'])
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success', 
                'data',
                'meta' => ['pagination']
            ]);
    }

    public function test_can_create_task_with_valid_data(): void
    {
        $taskData = [
            'title' => 'Nueva Tarea',
            'description' => 'Descripción',
            'status' => 'pendiente',
            'priority' => 1 // Requerido por validación
        ];

        $response = $this->withHeaders($this->auth['headers'])
            ->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(201);
        
        // Verificar que se guardó y que el created_by es el usuario actual (gracias al trait Auditable)
        $this->assertDatabaseHas('tasks', [
            'title' => 'Nueva Tarea',
            'created_by' => $this->auth['user']->id
        ]);
    }

    public function test_can_show_own_task(): void
    {
        // Crear tarea y asignar dueño manualmente (bypass fillable protection)
        $task = Task::factory()->create();
        $task->created_by = $this->auth['user']->id;
        $task->save();

        $response = $this->withHeaders($this->auth['headers'])
            ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $task->id);
    }

    public function test_cannot_show_others_task(): void
    {
        // Crear tarea de OTRO usuario
        $otherUser = User::factory()->create();
        $task = Task::factory()->create();
        $task->created_by = $otherUser->id;
        $task->save();

        // Intentar verla con el usuario del setUp ($this->auth)
        $response = $this->withHeaders($this->auth['headers'])
            ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(403); // Forbidden por Policy
    }

    public function test_can_update_own_task(): void
    {
        $task = Task::factory()->create();
        $task->created_by = $this->auth['user']->id;
        $task->save();
        
        $updateData = ['title' => 'Tarea Actualizada', 'status' => 'completada'];

        $response = $this->withHeaders($this->auth['headers'])
            ->putJson("/api/v1/tasks/{$task->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', ['title' => 'Tarea Actualizada']);
    }

    public function test_cannot_update_others_task(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create();
        $task->created_by = $otherUser->id;
        $task->save();

        $response = $this->withHeaders($this->auth['headers'])
            ->putJson("/api/v1/tasks/{$task->id}", ['title' => 'Hacker Update']);

        $response->assertStatus(403);
    }

    public function test_can_delete_own_task(): void
    {
        $task = Task::factory()->create();
        $task->created_by = $this->auth['user']->id;
        $task->save();

        $response = $this->withHeaders($this->auth['headers'])
            ->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
