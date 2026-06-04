<?php

namespace App\Actions\Collaborations;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Router;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Enums\Role;
use App\Http\Resources\CollaborationResource;
use App\Models\Collaboration;

class EditCollaboration
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('update', $request->route('collaboration'));
    }

    public static function routes(Router $router): void
    {
        $router->post('collaborations/{collaboration}/edit', static::class);
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(Role::class)],
        ];
    }

    public function handle(Collaboration $collaboration, array $validated): Collaboration
    {
        $currentRole = $collaboration->role;
        $newRole = Role::from($validated['role']);

        $collaboration->update($validated);

        return $collaboration;
    }

    public function asController(ActionRequest $request, Collaboration $collaboration): CollaborationResource
    {
        $collaboration = $this->handle($collaboration, $request->validated());

        return new CollaborationResource($collaboration);
    }
}
