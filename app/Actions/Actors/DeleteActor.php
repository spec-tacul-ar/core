<?php

namespace App\Actions\Actors;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Actor;

class DeleteActor
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('delete', $request->route('actor'));
    }

    public static function routes(Router $router): void
    {
        $router->post('actors/{actor}/delete', static::class);
    }

    public function handle(Actor $actor): void
    {
        $actor->delete();
    }

    public function asController(Actor $actor): Response
    {
        $this->handle($actor);

        return response()->noContent();
    }
}
