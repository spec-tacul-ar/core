<?php

namespace App\Actions\Projects;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Project;

class OrganiseProject
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('update', $request->route('project'));
    }

    public static function routes(Router $router): void
    {
        $router->post('projects/{project}/organise', static::class)
            ->middleware('sqids:actors.*.id,features.*.id,requirements.*.id,requirements.*.feature_id');
    }

    public function rules(): array
    {
        return [
            'actors' => ['nullable', 'array'],
            'actors.*.id' => ['required', 'integer', 'min:0'],
            'actors.*.weight' => ['required', 'integer', 'min:0', 'max:250'],
            'features' => ['nullable', 'array'],
            'features.*.id' => ['required', 'integer', 'min:0'],
            'features.*.weight' => ['required', 'integer', 'min:0', 'max:250'],
            'requirements' => ['nullable', 'array'],
            'requirements.*.id' => ['required', 'integer', 'min:0'],
            'requirements.*.feature_id' => ['required', 'integer', 'min:0'],
            'requirements.*.weight' => ['required', 'integer', 'min:0', 'max:250'],
        ];
    }

    public function handle(Project $project, array $validated): array
    {
        $project->load(['actors', 'features', 'requirements']);

        return DB::transaction(function () use ($project, $validated) {
            foreach ($validated['actors'] ?? [] as $actor) {
                $project->actors->find($actor['id'])->update($actor);
            }

            foreach ($validated['features'] ?? [] as $feature) {
                $project->features->find($feature['id'])->update($feature);
            }

            foreach ($validated['requirements'] ?? [] as $requirement) {
                if ($project->features->contains($requirement['feature_id'])) {
                    $project->requirements->find($requirement['id'])->update($requirement);
                }
            }

            return [
                'actors' => $project->actors->map(fn($actor) => [
                    'id' => $actor->sqid,
                    'weight' => $actor->weight,
                ])->toArray(),

                'features' => $project->features->map(fn($feature) => [
                    'id' => $feature->sqid,
                    'weight' => $feature->weight,
                ])->toArray(),

                'requirements' => $project->requirements->map(fn($requirement) => [
                    'id' => $requirement->sqid,
                    'feature_id' => $requirement->feature_sqid,
                    'weight' => $requirement->weight,
                ])->toArray(),
            ];
        });
    }

    public function asController(ActionRequest $request, Project $project): JsonResource
    {
        return new JsonResource($this->handle($project, $request->validated()));
    }
}
