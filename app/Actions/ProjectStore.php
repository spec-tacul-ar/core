<?php

namespace Spectacular\Core\Actions;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\ProjectResource;
use Spectacular\Core\Models\Project;
use Spectacular\Core\Rules\SluggableName as SluggableNameRule;

class ProjectStore
{
    use AsAction;

    public function rules(): array
    {
        return [
            'users' => ['array', 'max:25'],
            'users.*' => ['string'],
            'features' => ['array', 'max:25'],
            'features.*' => ['string'],
            'name' => ['required', 'string', 'max:255', new SluggableNameRule()],
        ];
    }

    public function handle(array $validated): Project
    {
        $project = Project::create($validated);

        if (array_key_exists('users', $validated)) {
            $users = array_map(fn ($item) => ['name' => $item], $validated['users']);

            $project->users()->createMany($users);
        }

        if (array_key_exists('features', $validated)) {
            $features = array_map(fn ($item) => ['name' => $item], $validated['features']);

            $project->features()->createMany($features);
        }

        return $project;
    }

    public function asController(ActionRequest $request): ProjectResource
    {
        return new ProjectResource($this->handle($request->validated()));
    }
}
