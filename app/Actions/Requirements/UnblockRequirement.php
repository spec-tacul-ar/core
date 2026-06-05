<?php

namespace App\Actions\Requirements;

use App\Http\Resources\RequirementResource;
use App\Models\Requirement;
use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UnblockRequirement
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('update', $request->route('requirement'));
    }

    public static function routes(Router $router): void
    {
        $router->post('requirements/{requirement}/unblock', static::class);
    }

    public function handle(Requirement $requirement): Requirement
    {
        $requirement->update(['blocked_reason' => null]);

        return $requirement;
    }

    public function asController(Requirement $requirement): RequirementResource
    {
        $requirement = $this->handle($requirement);

        return new RequirementResource($requirement);
    }
}
