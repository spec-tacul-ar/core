<?php

namespace App\Actions\Collaborations;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Collaboration;

class DeleteCollaboration
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('delete', $request->route('collaboration'));
    }

    public static function routes(Router $router): void
    {
        $router->post('collaborations/{collaboration}/delete', static::class);
    }

    public function handle(Collaboration $collaboration): void
    {
        $collaboration->delete();
    }

    public function asController(Collaboration $collaboration): Response
    {
        $this->handle($collaboration);

        return response()->noContent();
    }
}
