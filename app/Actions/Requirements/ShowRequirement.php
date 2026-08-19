<?php

namespace App\Actions\Requirements;

use App\Http\Resources\RequirementResource;
use App\Models\Requirement;
use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowRequirement
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('view', $request->route('requirement'));
    }

    public static function routes(Router $router): void
    {
        $router->get('requirements/{requirement}', static::class);
    }

    public function handle(Requirement $requirement): Requirement
    {
        return $requirement;
    }

    public function asController(Requirement $requirement): RequirementResource
    {
        return new RequirementResource($this->handle($requirement));
    }
}
