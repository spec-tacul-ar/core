<?php

namespace Tests\Feature;

use App\Events\ProjectItemSaved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class DeepProjectRelationTest extends TestCase
{
    use BuildsApiFixtures;
    use RefreshDatabase;

    public function test_indirect_project_items_resolve_and_eager_load_their_project(): void
    {
        $fixture = $this->createProjectFixture();

        foreach (['requirement', 'assignment', 'task', 'unknown'] as $item) {
            $model = $fixture[$item]::query()
                ->with('project')
                ->findOrFail($fixture[$item]->id);

            $this->assertTrue($model->relationLoaded('project'));
            $this->assertTrue($model->project->is($fixture['project']));
        }
    }

    public function test_saved_events_broadcast_indirect_project_items_to_their_project_channel(): void
    {
        $fixture = $this->createProjectFixture();

        foreach (['requirement', 'assignment', 'task', 'unknown'] as $item) {
            $channels = (new ProjectItemSaved($fixture[$item]))->broadcastOn();

            $this->assertSame('private-projects.' . $fixture['project']->sqid, $channels[0]->name);
        }
    }
}
