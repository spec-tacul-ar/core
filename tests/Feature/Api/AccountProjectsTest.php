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
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class AccountProjectsTest extends TestCase
{
    use BuildsApiFixtures;
    use RefreshDatabase;

    public function test_account_edit_endpoint_updates_the_authenticated_account(): void
    {
        $account = $this->actingAsAccount();

        $response = $this->postJson('/api/account/edit', [
            'name' => 'Renamed Account',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Renamed Account');
        $response->assertJsonPath('data.email', $account->email);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Renamed Account',
        ]);
    }

    public function test_account_delete_endpoint_deletes_the_account_and_owned_projects(): void
    {
        $account = Account::factory()->create();

        $ownedProject = Project::factory()->create();
        $this->attachContributor($account, $ownedProject, Role::OWNER);

        $sharedOwner = Account::factory()->create();
        $sharedProject = Project::factory()->create();
        $this->attachContributor($sharedOwner, $sharedProject, Role::OWNER);
        $this->attachContributor($account, $sharedProject, Role::VIEWER);

        $this->actingAsAccount($account);

        $response = $this->postJson('/api/account/delete', [
            'confirmation' => true,
        ]);

        $response->assertNoContent();

        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
        $this->assertSoftDeleted('projects', ['id' => $ownedProject->id]);
        $this->assertDatabaseHas('projects', ['id' => $sharedProject->id]);
        $this->assertDatabaseMissing('contributors', [
            'account_id' => $account->id,
            'project_id' => $sharedProject->id,
        ]);
    }

    public function test_projects_browse_endpoint_returns_only_visible_projects_with_aggregate_counts(): void
    {
        $account = Account::factory()->create();

        $alpha = Project::factory()->create(['name' => 'Alpha']);
        $beta = Project::factory()->create(['name' => 'Beta']);
        $hidden = Project::factory()->create(['name' => 'Hidden']);

        $this->attachContributor($account, $alpha, Role::OWNER);
        $this->attachContributor($account, $beta, Role::VIEWER);

        $feature = Feature::factory()->for($alpha)->create();
        $blockedRequirement = Requirement::factory()->for($feature)->create(['blocked_reason' => 'Waiting on vendor']);
        $openRequirement = Requirement::factory()->for($feature)->create(['blocked_reason' => null]);

        Task::factory()->for($blockedRequirement)->create(['is_complete' => true]);
        Task::factory()->for($openRequirement)->create(['is_complete' => false]);
        Unknown::factory()->for($blockedRequirement)->create();

        Feature::factory()->for($hidden)->create();

        $this->actingAsAccount($account);

        $response = $this->getJson('/api/projects/browse');

        $response->assertOk();
        $response->assertJsonPath('data.0.name', 'Alpha');
        $response->assertJsonPath('data.0.requirements_count', 2);
        $response->assertJsonPath('data.0.blocked_requirements_count', 1);
        $response->assertJsonPath('data.0.unknowns_count', 1);
        $response->assertJsonPath('data.0.tasks_count', 2);
        $response->assertJsonPath('data.0.requirements_with_tasks_count', 2);
        $response->assertJsonPath('data.0.requirements_all_tasks_complete_count', 1);
        $response->assertJsonPath('data.0.contributors.0.account_id', $account->sqid);
        $response->assertJsonMissingPath('data.0.contributors.0.account_name');
        $response->assertJsonPath('data.1.name', 'Beta');
        $response->assertJsonPath('meta.total', 2);
    }

    public function test_projects_browse_endpoint_does_not_include_solo_projects_for_real_accounts(): void
    {
        $account = Account::factory()->create();

        $owned = Project::factory()->create(['name' => 'Owned']);
        $solo = Project::factory()->create(['name' => 'Solo']);

        $this->attachContributor($account, $owned, Role::OWNER);

        $this->actingAsAccount($account);

        $response = $this->getJson('/api/projects/browse');

        $response->assertOk();
        $response->assertJsonPath('meta.total', 1);
        $response->assertJsonPath('data.0.name', 'Owned');
        $response->assertJsonMissing(['name' => 'Solo']);
    }

    public function test_projects_browse_endpoint_does_not_include_contributor_projects_in_solo_mode(): void
    {
        config(['spectacular.mode' => 'solo']);

        $solo = Project::factory()->create(['name' => 'Solo']);
        $owned = Project::factory()->create(['name' => 'Owned']);

        $this->attachContributor(Account::factory()->create(), $owned, Role::OWNER);

        Sanctum::actingAs(new Account([
            'id' => 0,
            'name' => 'Default',
            'email' => 'solo@spectacular',
        ]));

        $response = $this->getJson('/api/projects/browse');

        $response->assertOk();
        $response->assertJsonPath('meta.total', 1);
        $response->assertJsonPath('data.0.name', $solo->name);
        $response->assertJsonMissing(['name' => $owned->name]);
    }

    public function test_projects_add_endpoint_creates_a_project_and_attaches_the_authenticated_account_as_owner(): void
    {
        $account = $this->actingAsAccount();

        $response = $this->postJson('/api/projects/add', [
            'features' => ['Authentication'],
            'name' => 'Roadmap',
            'actors' => ['Operators'],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'Roadmap');

        $project = Project::query()->where('name', 'Roadmap')->firstOrFail();

        $this->assertDatabaseHas('contributors', [
            'account_id' => $account->id,
            'project_id' => $project->id,
            'role' => Role::OWNER->value,
        ]);
        $this->assertDatabaseHas('contributors', [
            'account_id' => $account->id,
            'project_id' => $project->id,
        ]);
        $this->assertDatabaseMissing('contributors', [
            'account_id' => $account->id,
            'project_id' => $project->id,
            'created_at' => null,
        ]);
        $this->assertDatabaseMissing('contributors', [
            'account_id' => $account->id,
            'project_id' => $project->id,
            'updated_at' => null,
        ]);
        $this->assertDatabaseHas('actors', [
            'name' => 'Operators',
            'project_id' => $project->id,
        ]);
        $this->assertDatabaseHas('features', [
            'name' => 'Authentication',
            'project_id' => $project->id,
        ]);
    }

    public function test_projects_demo_endpoint_leaves_demo_projects_unclaimed_in_solo_mode(): void
    {
        config(['spectacular.mode' => 'solo']);

        Sanctum::actingAs(new Account([
            'id' => 0,
            'name' => 'Default',
            'email' => 'solo@spectacular',
        ]));

        $response = $this->postJson('/api/projects/demo');

        $response->assertCreated();

        $project = (new Project())->resolveRouteBinding($response->json('data.id'));

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('contributors', ['project_id' => $project->id]);
    }

    public function test_projects_demo_endpoint_sets_contributor_timestamps_for_real_accounts(): void
    {
        $account = $this->actingAsAccount();

        $response = $this->postJson('/api/projects/demo');

        $response->assertCreated();

        $project = (new Project())->resolveRouteBinding($response->json('data.id'));

        $this->assertDatabaseHas('contributors', [
            'account_id' => $account->id,
            'project_id' => $project->id,
            'role' => Role::OWNER->value,
        ]);
        $this->assertDatabaseMissing('contributors', [
            'account_id' => $account->id,
            'project_id' => $project->id,
            'created_at' => null,
        ]);
        $this->assertDatabaseMissing('contributors', [
            'account_id' => $account->id,
            'project_id' => $project->id,
            'updated_at' => null,
        ]);
    }

    public function test_projects_read_endpoint_returns_hydrated_data_to_contributors_and_forbids_outsiders(): void
    {
        $fixture = $this->createProjectFixture(Role::VIEWER);
        $fixture['account']->markAsRead($fixture['project'], now()->subMinute());

        $this->actingAsAccount($fixture['account']);

        $response = $this->getJson('/api/projects/' . $fixture['project']->sqid . '/read?hydrated=1');

        $response->assertOk();
        $response->assertJsonPath('data.id', $fixture['project']->sqid);
        $response->assertJsonPath('data.actors.0.id', $fixture['projectActor']->sqid);
        $response->assertJsonPath('data.features.0.id', $fixture['feature']->sqid);
        $response->assertJsonPath('data.features.0.requirements.0.id', $fixture['requirement']->sqid);
        $response->assertJsonPath('data.features.0.requirements.0.assignments.0.actor_id', $fixture['projectActor']->sqid);
        $response->assertJsonPath('data.features.0.requirements.0.tasks.0.id', $fixture['task']->sqid);
        $response->assertJsonPath('data.features.0.requirements.0.unknowns.0.id', $fixture['unknown']->sqid);
        $response->assertJsonPath('data.contributors.0.account_name', $fixture['account']->name);
        $response->assertJsonPath('data.readmark', $fixture['account']->readmarks()->first()->updated_at->toJSON());

        $outsider = Account::factory()->create();
        $this->actingAsAccount($outsider);

        $this->getJson('/api/projects/' . $fixture['project']->sqid . '/read')->assertNotFound();
    }

    public function test_projects_read_endpoint_returns_not_found_for_malformed_sqids(): void
    {
        $this->actingAsAccount();

        $this->getJson('/api/projects/not-a-sqid/read')->assertNotFound();
    }

    public function test_projects_edit_endpoint_allows_editors_and_forbids_viewers(): void
    {
        $project = Project::factory()->create(['name' => 'Before']);

        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($editor);

        $this->postJson('/api/projects/' . $project->sqid . '/edit', [
            'description' => 'Updated description',
            'name' => 'After',
        ])->assertOk();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'description' => 'Updated description',
            'name' => 'After',
        ]);

        $this->actingAsAccount($viewer);

        $this->postJson('/api/projects/' . $project->sqid . '/edit', [
            'name' => 'Blocked',
        ])->assertForbidden();
    }

    public function test_projects_organise_endpoint_allows_editors_and_forbids_viewers(): void
    {
        $project = Project::factory()->create();
        $actor = Actor::factory()->for($project)->create(['weight' => 1]);
        $feature = Feature::factory()->for($project)->create(['weight' => 1]);
        $requirement = Requirement::factory()->for($feature)->create(['weight' => 1]);

        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($editor);

        $this->postJson('/api/projects/' . $project->sqid . '/organise', [
            'features' => [
                ['id' => $feature->sqid, 'weight' => 22],
            ],
            'requirements' => [
                ['id' => $requirement->sqid, 'feature_id' => $feature->sqid, 'weight' => 33],
            ],
            'actors' => [
                ['id' => $actor->sqid, 'weight' => 11],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('actors', ['id' => $actor->id, 'weight' => 11]);
        $this->assertDatabaseHas('features', ['id' => $feature->id, 'weight' => 22]);
        $this->assertDatabaseHas('requirements', ['id' => $requirement->id, 'weight' => 33]);

        $this->actingAsAccount($viewer);

        $this->postJson('/api/projects/' . $project->sqid . '/organise', [
            'features' => [],
            'requirements' => [],
            'actors' => [],
        ])->assertForbidden();
    }

    public function test_projects_organise_endpoint_accepts_partial_payloads(): void
    {
        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create(['weight' => 1]);
        $editor = Account::factory()->create();

        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->actingAsAccount($editor);

        $this->postJson('/api/projects/' . $project->sqid . '/organise', [
            'features' => [
                ['id' => $feature->sqid, 'weight' => 22],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'weight' => 22,
        ]);
    }

    public function test_projects_delete_endpoint_requires_owner_role(): void
    {
        $project = Project::factory()->create();

        $owner = Account::factory()->create();
        $editor = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);
        $this->attachContributor($editor, $project, Role::EDITOR);

        $this->actingAsAccount($editor);
        $this->postJson('/api/projects/' . $project->sqid . '/delete')->assertForbidden();

        $this->actingAsAccount($owner);
        $this->postJson('/api/projects/' . $project->sqid . '/delete')->assertNoContent();

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_projects_archive_endpoint_requires_owner_role(): void
    {
        $project = Project::factory()->create();

        $owner = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($owner);

        $response = $this->postJson('/api/projects/' . $project->sqid . '/archive');

        $response->assertOk();
        $response->assertJsonPath('data.id', $project->sqid);
        $this->assertNotNull($response->json('data.archived_at'));
        $this->assertNotNull($project->fresh()->archived_at);

        $project->restore();

        $this->actingAsAccount($viewer);

        $this->postJson('/api/projects/' . $project->sqid . '/archive')->assertForbidden();
        $this->assertNull($project->fresh()->archived_at);
    }

    public function test_projects_restore_endpoint_requires_owner_role(): void
    {
        $project = Project::factory()->archived()->create();

        $owner = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($owner);

        $response = $this->postJson('/api/projects/' . $project->sqid . '/restore');

        $response->assertOk();
        $response->assertJsonPath('data.id', $project->sqid);
        $response->assertJsonPath('data.archived_at', null);
        $this->assertNull($project->fresh()->archived_at);

        $project = $project->fresh();
        $project->archive();

        $this->actingAsAccount($viewer);

        $this->postJson('/api/projects/' . $project->sqid . '/restore')->assertForbidden();
        $this->assertNotNull($project->fresh()->archived_at);
    }

    public function test_projects_readmark_endpoint_marks_projects_as_read_for_contributors_and_forbids_outsiders(): void
    {
        $fixture = $this->createProjectFixture(Role::VIEWER);

        $this->actingAsAccount($fixture['account']);

        $response = $this->postJson('/api/projects/' . $fixture['project']->sqid . '/readmark');

        $response->assertOk();
        $response->assertJsonPath('data.id', $fixture['project']->sqid);
        $this->assertDatabaseHas('readmarks', [
            'account_id' => $fixture['account']->id,
            'project_id' => $fixture['project']->id,
        ]);

        $outsider = Account::factory()->create();
        $this->actingAsAccount($outsider);

        $this->postJson('/api/projects/' . $fixture['project']->sqid . '/readmark')->assertNotFound();
    }
}
