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
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class RevisionableUuidTest extends TestCase
{
    use BuildsApiFixtures;
    use RefreshDatabase;

    public function test_revisionable_models_use_integer_primary_keys_and_generate_uuids(): void
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
            $this->assertIsInt($model->getKey());
            $this->assertNotNull($model->uuid);
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
        $request->setUserResolver(fn() => $account);

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

    public function test_models_serialize_raw_ids_before_identifier_obfuscation(): void
    {
        $project = Project::factory()->create();
        $feature = $project->features()->create(['name' => 'Raw identifiers']);

        $raw = $feature->toArray();

        $this->assertSame($feature->id, $raw['id']);
        $this->assertSame($project->id, $raw['project_id']);
        $this->assertArrayNotHasKey('sqid', $raw);
        $this->assertArrayNotHasKey('project_sqid', $raw);

        $obfuscated = $feature->obfuscateIdentifiers($raw);

        $this->assertSame($feature->sqid, $obfuscated['id']);
        $this->assertSame($project->sqid, $obfuscated['project_id']);
        $this->assertArrayNotHasKey('sqid', $obfuscated);
        $this->assertArrayNotHasKey('project_sqid', $obfuscated);
    }
}
