<?php

declare(strict_types=1);

namespace Tests\Unit\Traits;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear tabla temporal para el test
        \Illuminate\Support\Facades\Schema::create('auditable_dummies', function ($table) {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->foreignId('deleted_by')->nullable();
        });
    }

    public function test_it_sets_created_by_and_updated_by_on_creation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $model = AuditableDummy::create(['title' => 'Test']);

        $this->assertEquals($user->id, $model->created_by);
        $this->assertEquals($user->id, $model->updated_by);
    }

    public function test_it_updates_updated_by_on_update(): void
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);
        $model = AuditableDummy::create(['title' => 'Original']);

        $editor = User::factory()->create();
        $this->actingAs($editor);
        
        $model->update(['title' => 'Updated']);

        $this->assertEquals($creator->id, $model->created_by);
        $this->assertEquals($editor->id, $model->updated_by);
    }

    public function test_it_sets_deleted_by_on_soft_delete(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $model = AuditableDummy::create(['title' => 'To Delete']);

        $deleter = User::factory()->create();
        $this->actingAs($deleter);
        
        $model->delete();

        $this->assertSoftDeleted('auditable_dummies', ['id' => $model->id]);
        
        $this->assertDatabaseHas('auditable_dummies', [
            'id' => $model->id,
            'deleted_by' => $deleter->id
        ]);
    }
}

class AuditableDummy extends \Illuminate\Database\Eloquent\Model
{
    use \App\Traits\Auditable, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'auditable_dummies';
    protected $guarded = [];
}
