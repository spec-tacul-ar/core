<?php

namespace Spectacular\Core\Actions\Projects;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\ProjectResource;
use Spectacular\Core\Models\Project;
use Spectacular\Core\Rules\SluggableName as SluggableNameRule;

class UpdateProject
{
    use AsAction;

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string'],
            'name' => ['sometimes', 'string', 'max:255', new SluggableNameRule()],
        ];
    }

    public function handle(Project $project, array $validated): Project
    {
        $project->update($validated);

        return $project;
    }

    public function asController(ActionRequest $request, Project $project): ProjectResource
    {
        return new ProjectResource($this->handle($project, $request->validated()));
    }
}
