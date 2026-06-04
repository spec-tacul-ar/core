<?php

namespace Tests\Feature;

use App\Models\Actor;
use App\Models\Feature;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightedScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_actors_are_ordered_by_weight(): void
    {
        $project = Project::factory()->create();

        Actor::factory()->for($project)->create(['name' => 'Second', 'weight' => 2]);
        Actor::factory()->for($project)->create(['name' => 'First', 'weight' => 1]);
        Actor::factory()->for($project)->create(['name' => 'Third', 'weight' => 3]);

        $this->assertSame(['First', 'Second', 'Third'], Actor::query()->pluck('name')->all());
    }

    public function test_weighted_scope_orders_equal_weights_by_model_key(): void
    {
        $project = Project::factory()->create();

        $first = Actor::factory()->for($project)->create(['weight' => 1]);
        $second = Actor::factory()->for($project)->create(['weight' => 1]);
        $third = Actor::factory()->for($project)->create(['weight' => 1]);

        $this->assertSame([$first->id, $second->id, $third->id], Actor::query()->pluck('id')->all());
    }

    public function test_features_are_ordered_by_weight(): void
    {
        $project = Project::factory()->create();

        Feature::factory()->for($project)->create(['name' => 'Second', 'weight' => 2]);
        Feature::factory()->for($project)->create(['name' => 'First', 'weight' => 1]);
        Feature::factory()->for($project)->create(['name' => 'Third', 'weight' => 3]);

        $this->assertSame(['First', 'Second', 'Third'], Feature::query()->pluck('name')->all());
    }

    public function test_requirements_are_ordered_by_weight(): void
    {
        $feature = Feature::factory()->for(Project::factory())->create();

        Requirement::factory()->for($feature)->create(['name' => 'second', 'weight' => 2]);
        Requirement::factory()->for($feature)->create(['name' => 'first', 'weight' => 1]);
        Requirement::factory()->for($feature)->create(['name' => 'third', 'weight' => 3]);

        $this->assertSame(['first', 'second', 'third'], Requirement::query()->pluck('name')->all());
    }

    public function test_tasks_are_ordered_by_weight(): void
    {
        $requirement = Requirement::factory()
            ->for(Feature::factory()->for(Project::factory()))
            ->create();

        Task::factory()->for($requirement)->create(['name' => 'Second', 'weight' => 2]);
        Task::factory()->for($requirement)->create(['name' => 'First', 'weight' => 1]);
        Task::factory()->for($requirement)->create(['name' => 'Third', 'weight' => 3]);

        $this->assertSame(['First', 'Second', 'Third'], Task::query()->pluck('name')->all());
    }

}
