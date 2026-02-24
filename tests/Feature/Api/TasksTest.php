<?php

namespace Tests\Feature\Api;

use Spectacular\Core\Models\Feature;
use Spectacular\Core\Models\Project;
use Spectacular\Core\Models\Requirement;
use Spectacular\Core\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_add_endpoint_creates_task(): void
    {
        $requirement = Requirement::factory()->for(Feature::factory()->for(Project::factory()))->create();

        $response = $this->postJson('/api/tasks/add', [
            'requirement_id' => $requirement->id,
            'name' => 'Create endpoint',
            'estimate' => 0.75,
            'is_complete' => false,
            'weight' => 4,
        ]);

        $response->assertCreated();

        $task = Task::firstOrFail();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'requirement_id' => $requirement->id,
            'name' => 'Create endpoint',
            'estimate' => 3,
            'is_complete' => false,
            'weight' => 4,
        ]);

        $response->assertJsonPath('data.id', $task->id);
    }

    public function test_tasks_edit_endpoint_updates_task(): void
    {
        $task = Task::factory()->for(Requirement::factory()->for(Feature::factory()->for(Project::factory())))->create(['name' => 'Initial']);

        $response = $this->postJson('/api/tasks/' . $task->id . '/edit', [
            'name' => 'Revised',
            'estimate' => 1.25,
            'is_complete' => true,
            'weight' => 8,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'name' => 'Revised',
            'estimate' => 5,
            'is_complete' => true,
            'weight' => 8,
        ]);
    }

    public function test_tasks_delete_endpoint_soft_deletes_task(): void
    {
        $task = Task::factory()->for(Requirement::factory()->for(Feature::factory()->for(Project::factory())))->create();

        $response = $this->postJson('/api/tasks/' . $task->id . '/delete');

        $response->assertNoContent();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
