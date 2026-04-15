<?php

namespace App\Actions\Actors;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\ActorResource;
use App\Models\Project;
use App\Models\Actor;
use Spatie\ValidationRules\Rules\Authorized;

class AddActor
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('create', Actor::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('actors/add', static::class);
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', new Authorized('update', Project::class)],
            'summary' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['nullable', 'integer', 'between:0,255'],
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
