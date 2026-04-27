<?php

namespace App\Actions\Requirements;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Requirement;

class DeleteRequirement
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('delete', $request->route('requirement'));
    }

    public static function routes(Router $router): void
    {
        $router->post('requirements/{requirement}/delete', static::class);
    }

    public function handle(Requirement $requirement): void
    {
        $requirement->delete();
    }

    public function asController(Requirement $requirement): Response
    {
        $this->handle($requirement);

        return response()->noContent();
    }
}
