<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class ProjectScopesTest extends TestCase
{
    use BuildsApiFixtures;
    use RefreshDatabase;

    public function test_owned_by_scope_returns_only_projects_owned_by_the_account(): void
    {
        $owner = Account::factory()->create();
        $otherAccount = Account::factory()->create();

        $ownedProject = Project::factory()->create(['name' => 'Owned']);
        $editableProject = Project::factory()->create(['name' => 'Editable']);
        $otherOwnedProject = Project::factory()->create(['name' => 'Other owned']);

        $this->attachContributor($owner, $ownedProject, Role::OWNER);
        $this->attachContributor($owner, $editableProject, Role::EDITOR);
        $this->attachContributor($otherAccount, $otherOwnedProject, Role::OWNER);

        $projects = Project::query()
            ->ownedBy($owner)
            ->pluck('id');

        $this->assertEqualsCanonicalizing([$ownedProject->id], $projects->all());
    }

    public function test_archived_scope_returns_only_archived_projects_by_default(): void
    {
        $archivedProject = Project::factory()->archived()->create();
        Project::factory()->create(['archived_at' => null]);

        $projects = Project::query()
            ->archived()
            ->pluck('id');

        $this->assertEqualsCanonicalizing([$archivedProject->id], $projects->all());
    }

    public function test_archived_scope_can_return_restored_projects(): void
    {
        Project::factory()->archived()->create();
        $restoredProject = Project::factory()->create(['archived_at' => null]);

        $projects = Project::query()
            ->archived(false)
            ->pluck('id');

        $this->assertEqualsCanonicalizing([$restoredProject->id], $projects->all());
    }
}
