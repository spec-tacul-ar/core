<?php

namespace Tests\Feature\Api;

use Spectacular\Core\Models\Project;
use Spectacular\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_add_endpoint_creates_user(): void
    {
        $project = Project::factory()->create();

        $response = $this->postJson('/api/users/add', [
            'project_id' => $project->id,
            'name' => 'Operations',
            'summary' => 'Platform and support users',
            'weight' => 6,
        ]);

        $response->assertCreated();

        $user = User::firstOrFail();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'project_id' => $project->id,
            'name' => 'Operations',
            'summary' => 'Platform and support users',
            'weight' => 6,
        ]);

        $response->assertJsonPath('data.id', $user->id);
    }

    public function test_users_edit_endpoint_updates_user(): void
    {
        $user = User::factory()->for(Project::factory())->create(['name' => 'Old users']);

        $response = $this->postJson('/api/users/' . $user->id . '/edit', [
            'name' => 'New users',
            'summary' => 'Updated summary',
            'weight' => 11,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New users',
            'summary' => 'Updated summary',
            'weight' => 11,
        ]);
    }

    public function test_users_delete_endpoint_soft_deletes_user(): void
    {
        $user = User::factory()->for(Project::factory())->create();

        $response = $this->postJson('/api/users/' . $user->id . '/delete');

        $response->assertNoContent();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
