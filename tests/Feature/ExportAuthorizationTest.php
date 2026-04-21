<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Actor;
use App\Models\Feature;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Task;
use App\Models\Unknown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class ExportAuthorizationTest extends TestCase
{
    use BuildsApiFixtures;
    use RefreshDatabase;

    public function test_project_json_export_requires_authentication(): void
    {
        Account::factory()->create();
        $project = Project::factory()->create();

        $this->getJson('/export/' . $project->id . '/json')->assertUnauthorized();
    }

    public function test_project_json_export_rejects_non_members(): void
    {
        $fixture = $this->createProjectFixture();
        $stranger = $this->actingAsAccount();

        $this->getJson('/export/' . $fixture['project']->id . '/json')->assertForbidden();
    }

    public function test_project_json_export_allows_project_members(): void
    {
        $fixture = $this->createProjectFixture(Role::VIEWER);
        $this->actingAsAccount($fixture['account']);

        $response = $this->getJson('/export/' . $fixture['project']->id . '/json');

        $response->assertOk();
        $response->assertJsonPath('name', $fixture['project']->name);
    }

    public function test_project_exports_only_return_the_requested_project(): void
    {
        $account = Account::factory()->create();
        $visibleProject = Project::factory()->create([
            'name' => 'Visible Export Project',
            'description' => 'Visible export description',
        ]);
        $hiddenProject = Project::factory()->create([
            'name' => 'Hidden Export Project',
            'description' => 'Hidden export description',
        ]);

        $this->attachContributor($account, $visibleProject, Role::VIEWER);
        $this->buildExportData($visibleProject);
        $this->buildExportData($hiddenProject);

        $this->actingAsAccount($account);

        $jsonResponse = $this->getJson('/export/' . $visibleProject->id . '/json');

        $jsonResponse->assertOk();
        $jsonResponse->assertJsonPath('name', 'Visible Export Project');
        $jsonResponse->assertJsonMissing(['name' => 'Hidden Export Project']);

        $this->get('/export/' . $visibleProject->id . '/html')
            ->assertOk()
            ->assertSeeText('Visible Export Project')
            ->assertDontSeeText('Hidden Export Project');

        $this->get('/export/' . $visibleProject->id . '/markdown')
            ->assertOk()
            ->assertSeeText('Visible Export Project')
            ->assertDontSeeText('Hidden Export Project');
    }

    public function test_project_exports_do_not_leak_to_non_members(): void
    {
        $member = Account::factory()->create();
        $outsider = Account::factory()->create();
        $project = Project::factory()->create();

        $this->attachContributor($member, $project, Role::VIEWER);

        $this->actingAsAccount($outsider);

        foreach (['json', 'html', 'markdown'] as $type) {
            $this->get('/export/' . $project->id . '/' . $type)->assertForbidden();
            $this->get('/api/export/' . $project->id . '/' . $type)->assertForbidden();
        }
    }

    public function test_project_exports_set_the_expected_content_type_headers(): void
    {
        $fixture = $this->createProjectFixture(Role::VIEWER);
        $this->actingAsAccount($fixture['account']);

        $this->get('/export/' . $fixture['project']->id . '/json')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');

        $this->get('/export/' . $fixture['project']->id . '/html')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');

        $this->get('/export/' . $fixture['project']->id . '/markdown')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/markdown; charset=utf-8');
    }

    public function test_api_project_export_route_requires_authentication_and_allows_members(): void
    {
        $fixture = $this->createProjectFixture(Role::VIEWER);

        $this->getJson('/api/export/' . $fixture['project']->id . '/json')->assertUnauthorized();

        $this->actingAsAccount($fixture['account']);

        $this->getJson('/api/export/' . $fixture['project']->id . '/json')
            ->assertOk()
            ->assertJsonPath('name', $fixture['project']->name);
    }

    public function test_project_exports_reject_unknown_export_types(): void
    {
        $fixture = $this->createProjectFixture(Role::VIEWER);
        $this->actingAsAccount($fixture['account']);

        $this->get('/export/' . $fixture['project']->id . '/xml')->assertNotFound();
        $this->get('/api/export/' . $fixture['project']->id . '/xml')->assertNotFound();
    }

    private function buildExportData(Project $project): void
    {
        $actor = Actor::factory()->for($project)->create(['name' => 'Export Actor']);
        $feature = Feature::factory()->for($project)->create(['name' => 'Export Feature']);
        $requirement = Requirement::factory()->for($feature)->create(['name' => 'export requirement']);

        $requirement->assignments()->create(['actor_id' => $actor->id]);
        Task::factory()->for($requirement)->create(['name' => 'Export Task']);
        Unknown::factory()->for($requirement)->create(['name' => 'Export Unknown?']);
    }
}
