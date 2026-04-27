<?php

namespace App\Actions\Actors;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\ActorResource;
use App\Models\Project;
use App\Models\Actor;
use App\Rules\Authorised;

class AddActor
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('create', Actor::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('actors/add', static::class)
            ->middleware('sqids:project_id');
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', new Authorised('update', Project::class)],
            'summary' => ['nullable', 'string', 'max:2500'],
            'name' => ['required', 'string', 'max:250'],
            'weight' => ['nullable', 'integer', 'between:0,250'],
        ];
    }

    public function handle(array $validated): Actor
    {
        return Actor::create($validated);
    }

    public function asController(ActionRequest $request): ActorResource
    {
        return new ActorResource($this->handle($request->validated()));
    }
}
