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

class ProjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_browse_endpoint_returns_projects_with_aggregate_counts(): void
    {
        $project = Project::factory()->create(['name' => 'Alpha']);

        $feature = Feature::factory()->for($project)->create();

        $blockedRequirement = Requirement::factory()->for($feature)->create(['blocked_reason' => 'Waiting on vendor']);
        $openRequirement = Requirement::factory()->for($feature)->create(['blocked_reason' => null]);

        Task::factory()->for($blockedRequirement)->create(['is_complete' => true]);
        Task::factory()->for($openRequirement)->create(['is_complete' => false]);

        Unknown::factory()->for($blockedRequirement)->create();

        Project::factory()->create(['name' => 'Zulu']);

        $response = $this->getJson('/api/projects/browse');

        $response->assertOk();
        $response->assertJsonPath('data.0.name', 'Alpha');
        $response->assertJsonPath('data.0.requirements_count', 2);
        $response->assertJsonPath('data.0.blocked_requirements_count', 1);
        $response->assertJsonPath('data.0.unknowns_count', 1);
        $response->assertJsonPath('data.0.tasks_count', 2);
        $response->assertJsonPath('data.0.requirements_with_tasks_count', 2);
        $response->assertJsonPath('data.0.requirements_all_tasks_complete_count', 1);
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.per_page', 50);
        $response->assertJsonPath('meta.total', 2);
    }

    public function test_projects_browse_endpoint_supports_page_query_parameter_with_fixed_page_size(): void
    {
        Project::factory()
            ->count(51)
            ->sequence(fn ($sequence) => ['name' => sprintf('Project %03d', $sequence->index + 1)])
            ->create();

        $response = $this->getJson('/api/projects/browse?page=2');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 50);
        $response->assertJsonPath('meta.total', 51);
        $response->assertJsonPath('meta.last_page', 2);
    }

    public function test_projects_add_endpoint_creates_project_with_users_and_features(): void
    {
        $response = $this->postJson('/api/projects/add', [
            'name' => 'Roadmap',
            'description' => 'Planned work for Q2.',
            'users' => ['Designers', 'Developers'],
            'features' => ['Authentication', 'Billing'],
        ]);

        $response->assertCreated();

        $project = Project::firstOrFail();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Roadmap',
            'description' => null,
        ]);

        $this->assertDatabaseHas('users', ['project_id' => $project->id, 'name' => 'Designers']);
        $this->assertDatabaseHas('users', ['project_id' => $project->id, 'name' => 'Developers']);
        $this->assertDatabaseHas('features', ['project_id' => $project->id, 'name' => 'Authentication']);
        $this->assertDatabaseHas('features', ['project_id' => $project->id, 'name' => 'Billing']);

        $response->assertJsonPath('data.name', 'Roadmap');
    }

    public function test_projects_read_endpoint_returns_hydrated_project_when_requested(): void
    {
        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();
        $user = User::factory()->for($project)->create();
        $requirement = Requirement::factory()->for($feature)->create();
        $task = Task::factory()->for($requirement)->create();
        $unknown = Unknown::factory()->for($requirement)->create();
        $assignment = $requirement->assignments()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson('/api/projects/' . $project->id . '/read?hydrated=1');

        $response->assertOk();
        $response->assertJsonPath('data.id', $project->id);
        $response->assertJsonPath('data.users.0.id', $user->id);
        $response->assertJsonPath('data.features.0.id', $feature->id);
        $response->assertJsonPath('data.features.0.requirements.0.id', $requirement->id);
        $response->assertJsonPath('data.features.0.requirements.0.tasks.0.id', $task->id);
        $response->assertJsonPath('data.features.0.requirements.0.unknowns.0.id', $unknown->id);
        $response->assertJsonPath('data.features.0.requirements.0.assignments.0.id', $assignment->id);
    }

    public function test_projects_edit_endpoint_updates_project(): void
    {
        $project = Project::factory()->create(['name' => 'Before']);

        $response = $this->postJson('/api/projects/' . $project->id . '/edit', [
            'name' => 'After',
            'description' => 'Updated description',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'After');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'After',
            'description' => 'Updated description',
        ]);
    }

    public function test_projects_delete_endpoint_removes_project_and_cascades_records(): void
    {
        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();
        $user = User::factory()->for($project)->create();
        $requirement = Requirement::factory()->for($feature)->create();
        $task = Task::factory()->for($requirement)->create();
        $unknown = Unknown::factory()->for($requirement)->create();
        $assignment = $requirement->assignments()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->postJson('/api/projects/' . $project->id . '/delete');

        $response->assertNoContent();

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('features', ['id' => $feature->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('requirements', ['id' => $requirement->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseMissing('unknowns', ['id' => $unknown->id]);
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
    }

    public function test_projects_organise_endpoint_updates_and_filters_payload_items(): void
    {
        $project = Project::factory()->create();
        $otherProject = Project::factory()->create();

        $projectUser = User::factory()->for($project)->create(['weight' => 1]);
        $projectFeature = Feature::factory()->for($project)->create(['weight' => 1]);

        $projectRequirement = Requirement::factory()->for($projectFeature)->create(['weight' => 1]);

        $otherFeature = Feature::factory()->for($otherProject)->create();
        $otherRequirement = Requirement::factory()->for($otherFeature)->create(['weight' => 2]);

        $response = $this->postJson('/api/projects/' . $project->id . '/organise', [
            'users' => [
                ['id' => $projectUser->id, 'weight' => 12],
            ],
            'features' => [
                ['id' => $projectFeature->id, 'weight' => 22],
            ],
            'requirements' => [
                ['id' => $projectRequirement->id, 'feature_id' => $projectFeature->id, 'weight' => 32],
                ['id' => $otherRequirement->id, 'feature_id' => $otherFeature->id, 'weight' => 99],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', ['id' => $projectUser->id, 'weight' => 12]);
        $this->assertDatabaseHas('features', ['id' => $projectFeature->id, 'weight' => 22]);
        $this->assertDatabaseHas('requirements', ['id' => $projectRequirement->id, 'weight' => 32]);
        $this->assertDatabaseHas('requirements', ['id' => $otherRequirement->id, 'weight' => 2]);

        $response->assertJsonPath('data.users.0.id', $projectUser->id);
        $response->assertJsonPath('data.features.0.id', $projectFeature->id);
    }
}
