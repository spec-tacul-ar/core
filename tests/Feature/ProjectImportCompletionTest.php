<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectImportCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_restores_requirement_completion_after_related_items_are_created(): void
    {
        $project = Project::import([
            'name' => 'Imported project',
            'actors' => [
                [
                    'id' => 1,
                    'name' => 'Users',
                ],
            ],
            'features' => [
                [
                    'name' => 'Feature',
                    'requirements' => [
                        [
                            'name' => 'ship the feature',
                            'activity_at' => '2026-08-18T10:00:00Z',
                            'completed_at' => '2026-08-18T10:01:00Z',
                            'tasks' => [
                                ['name' => 'Implement it', 'is_complete' => true],
                            ],
                            'unknowns' => [
                                ['name' => 'Does it need documentation?'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $requirement = $project->requirements()->firstOrFail();

        $this->assertSame('2026-08-18T10:00:00.000000Z', $requirement->activity_at->toJSON());
        $this->assertSame('2026-08-18T10:01:00.000000Z', $requirement->completed_at->toJSON());
        $this->assertTrue($requirement->is_complete);
    }
}
