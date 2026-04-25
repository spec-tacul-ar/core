<?php

namespace Tests\Feature;

use App\Http\Resources\ProjectResource;
use App\Models\Actor;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Task;
use App\Models\Unknown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class RevisionableUuidTest extends TestCase
{
    use BuildsApiFixtures;
    use RefreshDatabase;

    public function test_revisionable_models_generate_uuids(): void
    {
        $project = Project::factory()->create();
        $actor = Actor::factory()->for($project)->create();
        $feature = $project->features()->create(['name' => 'Feature']);
        $requirement = Requirement::factory()->for($feature)->create();
        $assignment = $requirement->assignments()->create([
            'actor_id' => $actor->id,
        ]);
        $task = Task::factory()->for($requirement)->create();
        $unknown = Unknown::factory()->for($requirement)->create();

        foreach ([$project, $actor, $feature, $requirement, $assignment, $task, $unknown] as $model) {
            $this->assertTrue(Str::isUuid($model->uuid));
        }
    }

    public function test_project_resource_does_not_include_uuids_for_revisionable_models(): void
    {
        $account = $this->actingAsAccount();
        $fixture = $this->createProjectFixture(account: $account);

        $project = $fixture['project']->fresh()->load([
            'actors',
            'features.requirements.assignments',
            'features.requirements.tasks',
            'features.requirements.unknowns',
        ]);

        $request = Request::create('/');
        $request->setUserResolver(fn () => $account);

        $payload = (new ProjectResource($project))
            ->response($request)
            ->getData(true);

        $data = $payload['data'];

        $this->assertArrayNotHasKey('uuid', $data);
        $this->assertArrayNotHasKey('uuid', $data['actors'][0]);
        $this->assertArrayNotHasKey('uuid', $data['features'][0]);
        $this->assertArrayNotHasKey('uuid', $data['features'][0]['requirements'][0]);
        $this->assertArrayNotHasKey('uuid', $data['features'][0]['requirements'][0]['assignments'][0]);
        $this->assertArrayNotHasKey('uuid', $data['features'][0]['requirements'][0]['tasks'][0]);
        $this->assertArrayNotHasKey('uuid', $data['features'][0]['requirements'][0]['unknowns'][0]);
    }
}
