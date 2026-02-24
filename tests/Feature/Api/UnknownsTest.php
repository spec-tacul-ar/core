<?php

namespace Tests\Feature\Api;

use Spectacular\Core\Models\Feature;
use Spectacular\Core\Models\Project;
use Spectacular\Core\Models\Requirement;
use Spectacular\Core\Models\Unknown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnknownsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknowns_add_endpoint_creates_unknown(): void
    {
        $requirement = Requirement::factory()->for(Feature::factory()->for(Project::factory()))->create();

        $response = $this->postJson('/api/unknowns/add', [
            'requirement_id' => $requirement->id,
            'name' => 'How will retries work?',
        ]);

        $response->assertCreated();

        $unknown = Unknown::firstOrFail();

        $this->assertDatabaseHas('unknowns', [
            'id' => $unknown->id,
            'requirement_id' => $requirement->id,
            'name' => 'How will retries work?',
        ]);

        $response->assertJsonPath('data.id', $unknown->id);
    }

    public function test_unknowns_edit_endpoint_updates_unknown(): void
    {
        $unknown = Unknown::factory()->for(Requirement::factory()->for(Feature::factory()->for(Project::factory())))->create(['name' => 'Old?']);

        $response = $this->postJson('/api/unknowns/' . $unknown->id . '/edit', [
            'name' => 'New?',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('unknowns', ['id' => $unknown->id, 'name' => 'New?']);
    }

    public function test_unknowns_delete_endpoint_soft_deletes_unknown(): void
    {
        $unknown = Unknown::factory()->for(Requirement::factory()->for(Feature::factory()->for(Project::factory())))->create();

        $response = $this->postJson('/api/unknowns/' . $unknown->id . '/delete');

        $response->assertNoContent();
        $this->assertSoftDeleted('unknowns', ['id' => $unknown->id]);
    }
}
