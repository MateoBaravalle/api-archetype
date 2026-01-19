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
        // Create tasks associated with the current user so they can view them if the viewAny policy filters (although current viewAny returns true)
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
            'priority' => 1 // Required by validation
        ];

        $response = $this->withHeaders($this->auth['headers'])
            ->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(201);
        
        // Verify that it was saved and that created_by is the current user (thanks to the Auditable trait)
        $this->assertDatabaseHas('tasks', [
            'title' => 'Nueva Tarea',
            'created_by' => $this->auth['user']->id
        ]);
    }

    public function test_can_show_own_task(): void
    {
        // Create task and assign owner manually (bypass fillable protection)
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
        // Create task for ANOTHER user
        $otherUser = User::factory()->create();
        $task = Task::factory()->create();
        $task->created_by = $otherUser->id;
        $task->save();

        // Try to view it with the user from setUp ($this->auth)
        $response = $this->withHeaders($this->auth['headers'])
            ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(403); // Forbidden by Policy
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
