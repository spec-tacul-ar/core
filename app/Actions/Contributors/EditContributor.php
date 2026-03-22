<?php

namespace App\Actions\Contributors;

use Illuminate\Routing\Router;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Enums\Role;
use App\Http\Resources\ContributorResource;
use App\Models\Contributor;

class EditContributor
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('update', $request->route('contributor'));
    }

    public static function routes(Router $router): void
    {
        $router->post('contributors/{contributor}/edit', static::class);
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(Role::class)],
        ];
    }

    public function handle(Contributor $contributor, array $validated): Contributor
    {
        $currentRole = $contributor->role;
        $newRole = Role::from($validated['role']);

        if ($newRole === Role::OWNER && $currentRole !== Role::OWNER) {
            abort(403);
        }

        $contributor->update($validated);

        return $contributor;
    }

    public function asController(ActionRequest $request, Contributor $contributor): ContributorResource
    {
        return new ContributorResource($this->handle($contributor, $request->validated()));
    }
}
