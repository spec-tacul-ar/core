<?php

namespace App\Actions\Projects;

use App\Enums\Role;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Rules\SluggableName as SluggableNameRule;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class AddProject
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('create', Project::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('projects/add', static::class);
    }

    public function rules(): array
    {
        return [
            'actors' => ['array', 'max:25'],
            'actors.*' => ['string'],
            'features' => ['array', 'max:25'],
            'features.*' => ['string'],
            'name' => ['required', 'string', 'max:255', new SluggableNameRule()],
        ];
    }

    public function handle(array $validated): Project
    {
        $project = Project::create($validated);

        if (array_key_exists('actors', $validated)) {
            $actors = array_map(fn($item) => ['name' => $item], $validated['actors']);

            $project->actors()->createMany($actors);
        }

        if (array_key_exists('features', $validated)) {
            $features = array_map(fn($item) => ['name' => $item], $validated['features']);

            $project->features()->createMany($features);
        }

        if (auth()->user()->exists) {
            auth()->user()->projects()->attach($project, ['role' => Role::OWNER]);
        }

        return $project;
    }

    public function asController(ActionRequest $request): ProjectResource
    {
        return new ProjectResource($this->handle($request->validated()));
    }
}
