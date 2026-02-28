<?php

namespace Tests\Feature\Api;

use Spectacular\Core\Models\Feature;
use Spectacular\Core\Models\Project;
use Spectacular\Core\Models\Requirement;
use Spectacular\Core\Models\Task;
use Spectacular\Core\Models\Unknown;
use Spectacular\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequirementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_requirements_add_endpoint_creates_requirement_with_related_records(): void
    {
        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();
        $user = User::factory()->for($project)->create();

        $response = $this->postJson('/api/requirements/add', [
            'feature_id' => $feature->id,
            'name' => 'send notifications',
            'description' => 'Require notifications for events.',
            'source' => 'Spec',
            'blocked_reason' => null,
            'weight' => 3,
            'user_ids' => [$user->id],
            'unknowns' => [
                ['name' => 'Which provider?'],
            ],
            'tasks' => [
                ['name' => 'Write implementation', 'is_complete' => false, 'estimate' => 1.5, 'weight' => 2],
            ],
        ]);

        $response->assertOk();

        $requirement = Requirement::firstOrFail();

        $this->assertDatabaseHas('requirements', [
            'id' => $requirement->id,
            'feature_id' => $feature->id,
            'name' => 'send notifications',
            'source' => 'Spec',
            'weight' => 3,
        ]);

        $this->assertDatabaseHas('assignments', [
            'requirement_id' => $requirement->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('unknowns', [
            'requirement_id' => $requirement->id,
            'name' => 'Which provider?',
        ]);
        $this->assertDatabaseHas('tasks', [
            'requirement_id' => $requirement->id,
            'name' => 'Write implementation',
            'is_complete' => false,
            'estimate' => 6,
            'weight' => 2,
        ]);

        $response->assertJsonPath('data.id', $requirement->id);
        $response->assertJsonPath('data.assignments.0.user_id', $user->id);
    }

    public function test_requirements_add_endpoint_rejects_user_ids_from_different_project_than_feature(): void
    {
        $featureProject = Project::factory()->create();
        $otherProject = Project::factory()->create();

        $feature = Feature::factory()->for($featureProject)->create();
        $otherProjectUser = User::factory()->for($otherProject)->create();

        $response = $this->postJson('/api/requirements/add', [
            'feature_id' => $feature->id,
            'name' => 'cross project assignment',
            'user_ids' => [$otherProjectUser->id],
            'unknowns' => [],
            'tasks' => [],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['user_ids.0']);
        $this->assertDatabaseCount('requirements', 0);
    }

    public function test_requirements_edit_endpoint_updates_requirement_and_syncs_relations(): void
    {
        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();

        $oldUser = User::factory()->for($project)->create();
        $newUser = User::factory()->for($project)->create();

        $requirement = Requirement::factory()->for($feature)->create(['name' => 'old name']);
        $requirement->assignments()->create(['user_id' => $oldUser->id]);

        $oldUnknown = Unknown::factory()->for($requirement)->create(['name' => 'Old unknown?']);
        $oldTask = Task::factory()->for($requirement)->create(['name' => 'Old task', 'is_complete' => false]);

        $response = $this->postJson('/api/requirements/' . $requirement->id . '/edit', [
            'name' => 'new name',
            'description' => 'Updated requirement',
            'source' => 'Updated source',
            'blocked_reason' => 'Dependency pending',
            'weight' => 10,
            'user_ids' => [$newUser->id],
            'unknowns' => [
                ['id' => $oldUnknown->id, 'name' => 'Updated unknown?'],
                ['name' => 'Fresh unknown?'],
            ],
            'tasks' => [
                ['id' => $oldTask->id, 'name' => 'Updated task', 'estimate' => 2.0, 'is_complete' => true, 'weight' => 6],
                ['name' => 'Fresh task', 'estimate' => 1.0, 'is_complete' => false, 'weight' => 7],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('requirements', [
            'id' => $requirement->id,
            'name' => 'new name',
            'description' => 'Updated requirement',
            'source' => 'Updated source',
            'blocked_reason' => 'Dependency pending',
            'weight' => 10,
        ]);

        $this->assertSoftDeleted('assignments', [
            'requirement_id' => $requirement->id,
            'user_id' => $oldUser->id,
        ]);
        $this->assertDatabaseHas('assignments', [
            'requirement_id' => $requirement->id,
            'user_id' => $newUser->id,
        ]);

        $this->assertDatabaseHas('unknowns', ['id' => $oldUnknown->id, 'name' => 'Updated unknown?']);
        $this->assertDatabaseHas('unknowns', ['requirement_id' => $requirement->id, 'name' => 'Fresh unknown?']);

        $this->assertDatabaseHas('tasks', [
            'id' => $oldTask->id,
            'name' => 'Updated task',
            'is_complete' => true,
            'estimate' => 8,
            'weight' => 6,
        ]);
        $this->assertDatabaseHas('tasks', [
            'requirement_id' => $requirement->id,
            'name' => 'Fresh task',
            'is_complete' => false,
            'estimate' => 4,
            'weight' => 7,
        ]);
    }

    public function test_requirements_edit_endpoint_rejects_user_ids_from_different_project_than_requirement_feature(): void
    {
        $featureProject = Project::factory()->create();
        $otherProject = Project::factory()->create();

        $feature = Feature::factory()->for($featureProject)->create();
        $requirement = Requirement::factory()->for($feature)->create();
        $otherProjectUser = User::factory()->for($otherProject)->create();

        $response = $this->postJson('/api/requirements/' . $requirement->id . '/edit', [
            'name' => 'still valid name',
            'user_ids' => [$otherProjectUser->id],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['user_ids.0']);
    }

    public function test_requirements_delete_endpoint_soft_deletes_requirement(): void
    {
        $requirement = Requirement::factory()->for(Feature::factory()->for(Project::factory()))->create();

        $response = $this->postJson('/api/requirements/' . $requirement->id . '/delete');

        $response->assertNoContent();
        $this->assertSoftDeleted('requirements', ['id' => $requirement->id]);
    }

    public function test_requirements_complete_tasks_endpoint_marks_all_tasks_complete(): void
    {
        $requirement = Requirement::factory()->for(Feature::factory()->for(Project::factory()))->create();

        $taskA = Task::factory()->for($requirement)->create(['is_complete' => false]);
        $taskB = Task::factory()->for($requirement)->create(['is_complete' => false]);

        $response = $this->postJson('/api/requirements/' . $requirement->id . '/tasks/complete');

        $response->assertOk();

        $this->assertDatabaseHas('tasks', ['id' => $taskA->id, 'is_complete' => true]);
        $this->assertDatabaseHas('tasks', ['id' => $taskB->id, 'is_complete' => true]);

        $response->assertJsonPath('data.0.is_complete', true);
        $response->assertJsonPath('data.1.is_complete', true);
    }
}
