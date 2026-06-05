<?php

namespace App\Actions\Requirements;

use App\Http\Resources\RequirementResource;
use App\Models\Requirement;
use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class BlockRequirement
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('update', $request->route('requirement'));
    }

    public static function routes(Router $router): void
    {
        $router->post('requirements/{requirement}/block', static::class);
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:250'],
        ];
    }

    public function handle(Requirement $requirement, array $validated): Requirement
    {
        $requirement->update(['blocked_reason' => $validated['reason']]);

        return $requirement;
    }

    public function asController(ActionRequest $request, Requirement $requirement): RequirementResource
    {
        $validated = $request->validated();

        $requirement = $this->handle($requirement, $validated);

        return new RequirementResource($requirement);
    }
}
