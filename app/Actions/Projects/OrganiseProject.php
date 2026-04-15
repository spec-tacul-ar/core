<?php

namespace App\Actions\Projects;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Project;

class OrganiseProject
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('update', $request->route('project'));
    }

    public static function routes(Router $router): void
    {
        $router->post('projects/{project}/organise', static::class);
    }

    public function rules(): array
    {
        return [
            'actors' => ['nullable', 'array'],
            'actors.*.id' => ['required', 'integer', 'min:0'],
            'actors.*.weight' => ['required', 'integer', 'min:0', 'max:255'],
            'features' => ['nullable', 'array'],
            'features.*.id' => ['required', 'integer', 'min:0'],
            'features.*.weight' => ['required', 'integer', 'min:0', 'max:255'],
            'requirements' => ['nullable', 'array'],
            'requirements.*.id' => ['required', 'integer', 'min:0'],
            'requirements.*.feature_id' => ['required', 'integer', 'min:0'],
            'requirements.*.weight' => ['required', 'integer', 'min:0', 'max:255'],
        ];
    }

    public function handle(Project $project, array $validated): array
    {
        $project->load(['actors', 'features', 'requirements']);

        return DB::transaction(function () use ($project, $validated) {
            foreach ($validated['actors'] as $actor) {
                $project->actors->find($actor['id'])->update($actor);
            }

            foreach ($validated['features'] as $feature) {
                $project->features->find($feature['id'])->update($feature);
            }

            foreach ($validated['requirements'] as $requirement) {
                if ($project->features->contains($requirement['feature_id'])) {
                    $project->requirements->find($requirement['id'])->update($requirement);
                }
            }

            return [
                'actors' => $project->actors->map->only(['id', 'weight'])->toArray(),
                'features' => $project->features->map->only(['id', 'weight'])->toArray(),
                'requirements' => $project->requirements->map->only(['id', 'feature_id', 'weight'])->toArray(),
            ];
        });
    }

    public function asController(ActionRequest $request, Project $project): JsonResource
    {
        return new JsonResource($this->handle($project, $request->validated()));
    }
}
