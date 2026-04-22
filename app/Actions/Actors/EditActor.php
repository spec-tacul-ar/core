<?php

namespace App\Actions\Actors;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\ActorResource;
use App\Models\Actor;

class EditActor
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('update', $request->route('actor'));
    }

    public static function routes(Router $router): void
    {
        $router->post('actors/{actor}/edit', static::class);
    }

    public function rules(): array
    {
        return [
            'summary' => ['nullable', 'string', 'max:2500'],
            'name' => ['required', 'string', 'max:250'],
            'weight' => ['nullable', 'integer', 'between:0,250'],
        ];
    }

    public function handle(Actor $actor, array $validated): Actor
    {
        $actor->update($validated);

        return $actor;
    }

    public function asController(ActionRequest $request, Actor $actor): ActorResource
    {
        return new ActorResource($this->handle($actor, $request->validated()));
    }
}
