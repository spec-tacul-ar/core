<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RemoveEstimatesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_estimates_are_appended_to_names_before_the_column_is_removed(): void
    {
        $migration = require database_path('migrations/2026_06_03_000001_remove_estimates_from_projects_and_tasks.php');
        $migration->down();

        $requirement = Requirement::factory()
            ->for(Feature::factory()->for(Project::factory()))
            ->create();

        $estimatedTask = Task::factory()->for($requirement)->create([
            'name' => 'Review implementation',
        ]);
        $singularTask = Task::factory()->for($requirement)->create([
            'name' => str_repeat('A', 250),
        ]);
        $unestimatedTask = Task::factory()->for($requirement)->create([
            'name' => 'No estimate',
        ]);

        DB::table('tasks')->where('id', $estimatedTask->id)->update(['estimate' => 15]);
        DB::table('tasks')->where('id', $singularTask->id)->update(['estimate' => 4]);

        $migration->up();

        $singularName = DB::table('tasks')->where('id', $singularTask->id)->value('name');

        $this->assertFalse(Schema::hasColumn('tasks', 'estimate'));
        $this->assertFalse(Schema::hasColumn('projects', 'hide_estimates'));
        $this->assertDatabaseHas('tasks', [
            'id' => $estimatedTask->id,
            'name' => 'Review implementation (Estimate: 3.75 hours)',
        ]);
        $this->assertSame('No estimate', DB::table('tasks')->where('id', $unestimatedTask->id)->value('name'));
        $this->assertSame(250, mb_strlen($singularName));
        $this->assertStringEndsWith(' (Estimate: 1 hour)', $singularName);
    }
}
