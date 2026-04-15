<?php

namespace Tests\Feature\Api;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Feature;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Task;
use App\Models\Unknown;
use App\Models\Actor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class NestedResourcesTest extends TestCase
{
    use BuildsApiFixtures;
    use RefreshDatabase;

    public function test_features_endpoints_require_project_edit_access(): void
    {
        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create(['name' => 'Initial']);

        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($editor);
        $this->postJson('/api/features/add', [
            'description' => 'Feature details',
            'name' => 'Workflow',
            'project_id' => $project->id,
            'weight' => 5,
        ])->assertCreated();

        $createdFeature = Feature::query()->where('name', 'Workflow')->firstOrFail();
        $this->assertDatabaseHas('features', [
            'id' => $createdFeature->id,
            'project_id' => $project->id,
            'weight' => 5,
        ]);

        $this->actingAsAccount($viewer);
        $this->postJson('/api/features/add', [
            'name' => 'Blocked feature',
            'project_id' => $project->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('project_id');

        $this->postJson('/api/features/' . $feature->id . '/edit', [
            'name' => 'Blocked edit',
        ])->assertForbidden();

        $this->postJson('/api/features/' . $feature->id . '/delete')->assertForbidden();

        $this->actingAsAccount($editor);
        $this->postJson('/api/features/' . $feature->id . '/edit', [
            'description' => 'Changed',
            'name' => 'Updated',
        ])->assertOk();

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'description' => 'Changed',
            'name' => 'Updated',
        ]);

        $this->postJson('/api/features/' . $feature->id . '/delete')->assertNoContent();
        $this->assertSoftDeleted('features', ['id' => $feature->id]);
    }

    public function test_actors_endpoints_require_project_edit_access(): void
    {
        $project = Project::factory()->create();
        $actor = Actor::factory()->for($project)->create(['name' => 'Initial users']);

        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($editor);
        $this->postJson('/api/actors/add', [
            'name' => 'Operators',
            'project_id' => $project->id,
            'summary' => 'Platform users',
            'weight' => 6,
        ])->assertCreated();

        $createdActor = Actor::query()->where('name', 'Operators')->firstOrFail();
        $this->assertDatabaseHas('actors', [
            'id' => $createdActor->id,
            'project_id' => $project->id,
            'weight' => 6,
        ]);

        $this->actingAsAccount($viewer);
        $this->postJson('/api/actors/add', [
            'name' => 'Blocked users',
            'project_id' => $project->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('project_id');

        $this->postJson('/api/actors/' . $actor->id . '/edit', [
            'name' => 'Blocked update',
        ])->assertForbidden();

        $this->postJson('/api/actors/' . $actor->id . '/delete')->assertForbidden();

        $this->actingAsAccount($editor);
        $this->postJson('/api/actors/' . $actor->id . '/edit', [
            'name' => 'Updated users',
            'summary' => 'Updated summary',
            'weight' => 11,
        ])->assertOk();

        $this->assertDatabaseHas('actors', [
            'id' => $actor->id,
            'name' => 'Updated users',
            'summary' => 'Updated summary',
            'weight' => 11,
        ]);

        $this->postJson('/api/actors/' . $actor->id . '/delete')->assertNoContent();
        $this->assertSoftDeleted('actors', ['id' => $actor->id]);
    }

    public function test_requirements_add_and_edit_endpoints_require_project_edit_access_and_reject_cross_project_actors(): void
    {
        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();
        $requirement = Requirement::factory()->for($feature)->create(['name' => 'Initial requirement']);
        $projectActor = Actor::factory()->for($project)->create();
        $otherProjectActor = Actor::factory()->for(Project::factory())->create();
        $existingUnknown = Unknown::factory()->for($requirement)->create(['name' => 'Old unknown?']);
        $existingTask = Task::factory()->for($requirement)->create(['name' => 'Old task', 'is_complete' => false]);

        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($editor);
        $this->postJson('/api/requirements/add', [
            'feature_id' => $feature->id,
            'name' => 'deliver notifications',
            'tasks' => [
                ['estimate' => 1.5, 'is_complete' => false, 'name' => 'Write implementation', 'weight' => 2],
            ],
            'unknowns' => [
                ['name' => 'Which provider?'],
            ],
            'actor_ids' => [$projectActor->id],
            'weight' => 3,
        ])->assertOk();

        $createdRequirement = Requirement::query()->where('name', 'deliver notifications')->firstOrFail();
        $this->assertDatabaseHas('assignments', [
            'requirement_id' => $createdRequirement->id,
            'actor_id' => $projectActor->id,
        ]);
        $this->assertDatabaseHas('tasks', [
            'requirement_id' => $createdRequirement->id,
            'name' => 'Write implementation',
        ]);
        $this->assertDatabaseHas('unknowns', [
            'requirement_id' => $createdRequirement->id,
            'name' => 'Which provider?',
        ]);

        $this->actingAsAccount($viewer);
        $this->postJson('/api/requirements/add', [
            'feature_id' => $feature->id,
            'name' => 'blocked requirement',
            'tasks' => [],
            'unknowns' => [],
            'actor_ids' => [],
        ])->assertUnprocessable()->assertJsonValidationErrors('feature_id');

        $this->actingAsAccount($editor);
        $this->postJson('/api/requirements/add', [
            'feature_id' => $feature->id,
            'name' => 'cross project assignment',
            'tasks' => [],
            'unknowns' => [],
            'actor_ids' => [$otherProjectActor->id],
        ])->assertUnprocessable()->assertJsonValidationErrors('actor_ids.0');

        $this->actingAsAccount($viewer);
        $this->postJson('/api/requirements/' . $requirement->id . '/edit', [
            'name' => 'Blocked update',
        ])->assertForbidden();

        $this->actingAsAccount($editor);
        $this->postJson('/api/requirements/' . $requirement->id . '/edit', [
            'name' => 'Updated requirement',
            'tasks' => [
                ['estimate' => 2.0, 'id' => $existingTask->id, 'is_complete' => true, 'name' => 'Updated task', 'weight' => 6],
                ['estimate' => 1.0, 'is_complete' => false, 'name' => 'Fresh task', 'weight' => 7],
            ],
            'unknowns' => [
                ['id' => $existingUnknown->id, 'name' => 'Updated unknown?'],
                ['name' => 'Fresh unknown?'],
            ],
            'actor_ids' => [$projectActor->id],
            'weight' => 10,
        ])->assertOk();

        $this->assertDatabaseHas('requirements', [
            'id' => $requirement->id,
            'name' => 'Updated requirement',
            'weight' => 10,
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $existingTask->id,
            'is_complete' => true,
            'name' => 'Updated task',
            'weight' => 6,
        ]);
        $this->assertDatabaseHas('unknowns', [
            'id' => $existingUnknown->id,
            'name' => 'Updated unknown?',
        ]);
        $this->assertDatabaseHas('assignments', [
            'requirement_id' => $requirement->id,
            'actor_id' => $projectActor->id,
        ]);
    }

    public function test_requirements_complete_and_delete_endpoints_require_project_edit_access(): void
    {
        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();
        $requirement = Requirement::factory()->for($feature)->create();
        $taskA = Task::factory()->for($requirement)->create(['is_complete' => false]);
        $taskB = Task::factory()->for($requirement)->create(['is_complete' => false]);

        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($viewer);
        $this->postJson('/api/requirements/' . $requirement->id . '/tasks/complete')->assertForbidden();
        $this->postJson('/api/requirements/' . $requirement->id . '/delete')->assertForbidden();

        $this->actingAsAccount($editor);
        $this->postJson('/api/requirements/' . $requirement->id . '/tasks/complete')->assertOk();

        $this->assertDatabaseHas('tasks', ['id' => $taskA->id, 'is_complete' => true]);
        $this->assertDatabaseHas('tasks', ['id' => $taskB->id, 'is_complete' => true]);

        $this->postJson('/api/requirements/' . $requirement->id . '/delete')->assertNoContent();
        $this->assertSoftDeleted('requirements', ['id' => $requirement->id]);
    }

    public function test_tasks_endpoints_require_project_edit_access(): void
    {
        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();
        $requirement = Requirement::factory()->for($feature)->create();
        $task = Task::factory()->for($requirement)->create(['name' => 'Initial']);

        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($editor);
        $this->postJson('/api/tasks/add', [
            'estimate' => 0.75,
            'is_complete' => false,
            'name' => 'Create endpoint',
            'requirement_id' => $requirement->id,
            'weight' => 4,
        ])->assertCreated();

        $createdTask = Task::query()->where('name', 'Create endpoint')->firstOrFail();
        $this->assertDatabaseHas('tasks', [
            'id' => $createdTask->id,
            'requirement_id' => $requirement->id,
            'weight' => 4,
        ]);

        $this->actingAsAccount($viewer);
        $this->postJson('/api/tasks/add', [
            'is_complete' => false,
            'name' => 'Blocked task',
            'requirement_id' => $requirement->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('requirement_id');

        $this->postJson('/api/tasks/' . $task->id . '/edit', [
            'name' => 'Blocked task update',
        ])->assertForbidden();

        $this->postJson('/api/tasks/' . $task->id . '/delete')->assertForbidden();

        $this->actingAsAccount($editor);
        $this->postJson('/api/tasks/' . $task->id . '/edit', [
            'estimate' => 1.25,
            'is_complete' => true,
            'name' => 'Updated task',
            'weight' => 8,
        ])->assertOk();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_complete' => true,
            'name' => 'Updated task',
            'weight' => 8,
        ]);

        $this->postJson('/api/tasks/' . $task->id . '/delete')->assertNoContent();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_unknowns_endpoints_require_project_edit_access(): void
    {
        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();
        $requirement = Requirement::factory()->for($feature)->create();
        $unknown = Unknown::factory()->for($requirement)->create(['name' => 'Old?']);

        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($editor);
        $this->postJson('/api/unknowns/add', [
            'name' => 'How will retries work?',
            'requirement_id' => $requirement->id,
        ])->assertCreated();

        $createdUnknown = Unknown::query()->where('name', 'How will retries work?')->firstOrFail();
        $this->assertDatabaseHas('unknowns', [
            'id' => $createdUnknown->id,
            'requirement_id' => $requirement->id,
        ]);

        $this->actingAsAccount($viewer);
        $this->postJson('/api/unknowns/add', [
            'name' => 'Blocked unknown?',
            'requirement_id' => $requirement->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('requirement_id');

        $this->postJson('/api/unknowns/' . $unknown->id . '/edit', [
            'name' => 'Blocked?',
        ])->assertForbidden();

        $this->postJson('/api/unknowns/' . $unknown->id . '/delete')->assertForbidden();

        $this->actingAsAccount($editor);
        $this->postJson('/api/unknowns/' . $unknown->id . '/edit', [
            'name' => 'New?',
        ])->assertOk();

        $this->assertDatabaseHas('unknowns', [
            'id' => $unknown->id,
            'name' => 'New?',
        ]);

        $this->postJson('/api/unknowns/' . $unknown->id . '/delete')->assertNoContent();
        $this->assertSoftDeleted('unknowns', ['id' => $unknown->id]);
    }
}
