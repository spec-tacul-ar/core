<?php

namespace Tests\Feature\Api;

use Spectacular\Core\Models\Feature;
use Spectacular\Core\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_features_add_endpoint_creates_feature(): void
    {
        $project = Project::factory()->create();

        $response = $this->postJson('/api/features/add', [
            'project_id' => $project->id,
            'name' => 'Workflow',
            'description' => 'Feature details',
            'weight' => 5,
        ]);

        $response->assertCreated();

        $feature = Feature::firstOrFail();

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'project_id' => $project->id,
            'name' => 'Workflow',
            'weight' => 5,
        ]);

        $response->assertJsonPath('data.id', $feature->id);
    }

    public function test_features_edit_endpoint_updates_feature(): void
    {
        $feature = Feature::factory()->for(Project::factory())->create(['name' => 'Old']);

        $response = $this->postJson('/api/features/' . $feature->id . '/edit', [
            'name' => 'New',
            'description' => 'Changed',
            'weight' => 9,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('features', [
            'id' => $feature->id,
            'name' => 'New',
            'description' => 'Changed',
            'weight' => 9,
        ]);
    }

    public function test_features_delete_endpoint_soft_deletes_feature(): void
    {
        $feature = Feature::factory()->for(Project::factory())->create();

        $response = $this->postJson('/api/features/' . $feature->id . '/delete');

        $response->assertNoContent();
        $this->assertSoftDeleted('features', ['id' => $feature->id]);
    }
}
