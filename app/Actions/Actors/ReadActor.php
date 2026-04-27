<?php

namespace App\Actions\Actors;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\ActorResource;
use App\Models\Actor;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ReadActor
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('view', $request->route('actor'));
    }

    public static function routes(Router $router): void
    {
        $router->get('actors/{actor}/read', static::class);
    }

    public function asController(Actor $actor): ActorResource
    {
        return new ActorResource($actor);
    }
}
