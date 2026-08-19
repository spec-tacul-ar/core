<?php

namespace Tests\Feature;

use App\Models\Project;
use Database\Seeders\TestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_varied_requirement_data(): void
    {
        $this->seed(TestSeeder::class);

        $project = Project::query()->where('name', 'My Project')->firstOrFail();

        $this->assertTrue($project->tasks()->where('is_complete', true)->exists());
        $this->assertTrue($project->tasks()->where('is_complete', false)->exists());

        $featureRequirementCounts = $project->features()
            ->withCount('requirements')
            ->pluck('requirements_count');
        $requirements = $project->requirements()
            ->withCount(['assignments', 'tasks', 'unknowns'])
            ->get();

        $this->assertGreaterThan(1, $featureRequirementCounts->unique()->count());
        $this->assertGreaterThan(1, $requirements->pluck('assignments_count')->unique()->count());
        $this->assertGreaterThan(1, $requirements->pluck('tasks_count')->unique()->count());
        $this->assertGreaterThan(1, $requirements->pluck('unknowns_count')->unique()->count());
    }
}
