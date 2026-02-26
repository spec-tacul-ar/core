<?php

namespace Spectacular\Core\Actions;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\Project;

class ProjectOrganise
{
    use AsAction;

    public function rules(): array
    {
        return [
            'users' => ['nullable', 'array'],
            'users.*.id' => ['required', 'integer', 'min:0'],
            'users.*.weight' => ['required', 'integer', 'min:0', 'max:255'],
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
        $project->load(['users', 'features', 'requirements']);

        return DB::transaction(function () use ($project, $validated) {
            foreach ($validated['users'] as $user) {
                $project->users->find($user['id'])->update($user);
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
                'users' => $project->users->map->only(['id', 'weight'])->toArray(),
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
