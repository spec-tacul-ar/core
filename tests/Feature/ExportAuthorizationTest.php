<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Project;
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
}
