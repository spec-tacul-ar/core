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
}
