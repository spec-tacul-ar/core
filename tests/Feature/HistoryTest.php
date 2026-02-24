<?php

namespace Tests\Feature;

use Spectacular\Core\Models\Feature;
use Spectacular\Core\Models\Project;
use Spectacular\Core\Models\Requirement;
use Spectacular\Core\Models\Task;
use Spectacular\Core\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use LogicException;
use Tests\TestCase;

class HistoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_projects_create_revisions_on_update(): void
    {
        $project = Project::factory()->create(['name' => 'Old Name']);

        $project->name = 'New Name';
        $project->save();

        $this->assertCount(1, $project->history);
        $this->assertEquals(['name' => 'Old Name'], $project->history->last()['data']);
    }

    public function test_features_create_revisions_on_delete(): void
    {
        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();

        $feature->delete();

        $this->assertEquals(['deleted_at' => null], $feature->history->last()['data']);
    }

    public function test_projects_roll_back(): void
    {
        $this->travel(-2)->seconds();

        $project = Project::factory()->create(['name' => 'Old Name']);

        $this->travelBack();

        $project->name = 'New Name';
        $project->save();

        $project->rollBack(now()->subSecond());

        $this->assertEquals('Old Name', $project->name);
    }

    public function test_projects_cannot_roll_back_before_they_were_made(): void
    {
        $this->expectException(LogicException::class);

        $this->travel(-1)->seconds();

        $project = Project::factory()->create(['name' => 'Old Name']);

        $this->travelBack();

        $project->rollBack(now()->subSeconds(2));
    }

    public function test_features_can_be_rolled_back(): void
    {
        $this->travel(-2)->seconds();

        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create(['name' => 'Old Name']);

        $this->travelBack();

        $feature->name = 'New Name';
        $feature->save();

        $project->rollBack(now()->subSecond(), ['features']);

        $this->assertEquals('Old Name', $project->features->first()->name);
    }

    public function test_features_can_be_rolled_back_after_soft_deleting()
    {
        $this->travel(-2)->seconds();

        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();

        $this->travelBack();

        $feature->delete();

        $project->rollBack(now()->subSecond(), ['features']);

        $this->assertFalse($project->features->first()->trashed());
    }

    public function test_deleted_features_are_not_revived_after_roll_back(): void
    {
        $this->travel(-2)->seconds();

        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();

        $feature->delete();

        $this->travelBack();

        $project->rollBack(now()->subSecond(), ['features']);

        $this->assertTrue($project->features->isEmpty());
    }

    public function test_features_made_after_the_timestamp_are_not_included(): void
    {
        $this->travelTo(now()->subSeconds(2));

        $project = Project::factory()->create();

        $this->travelBack();

        $feature = Feature::factory()->for($project)->create();

        $project->rollBack(now()->subSecond(1), ['features']);

        $this->assertTrue($project->features->isEmpty());
    }

    public function test_relations_added_after_the_timestamp_are_not_included_after_roll_back(): void
    {
        $this->travelTo(now()->subSeconds(2));

        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();

        $this->travelBack();

        Requirement::factory()->for($feature)->create();

        $project->rollBack(now()->subSecond(1), ['features.requirements']);

        $this->assertTrue($project->features->first()->requirements->isEmpty());
    }

    public function test_has_many_through_rolls_back(): void
    {
        $this->travelTo(now()->subSeconds(2));

        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();
        $requirement = Requirement::factory()->for($feature)->create();

        $this->travelBack();

        $feature->delete();

        $project->rollBack(now()->subSecond(1), ['requirements']);

        $this->assertTrue($project->requirements->isNotEmpty());
    }

    public function test_eager_loading_rolls_back(): void
    {
        $this->travelTo(now()->subSeconds(2));

        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();
        $requirement = Requirement::factory()->for($feature)->create(['name' => 'Old Name']);

        $this->travelBack();

        $requirement->name = 'New Name';
        $requirement->save();

        $project->rollBack(now()->subSecond(), ['features.requirements']);

        $this->assertEquals('Old Name', $project->features->first()->requirements->first()->name);
    }
}
