<?php

namespace App\Actions\Users;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\UserResource;
use App\Models\Project;
use App\Models\User;
use Spatie\ValidationRules\Rules\Authorized;

class AddUser
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('create', User::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('users/add', static::class);
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', new Authorized('update', Project::class)],
            'summary' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['nullable', 'integer', 'between:0,255'],
        ];
    }

    public function handle(array $validated): User
    {
        return User::create($validated);
    }

    public function asController(ActionRequest $request): UserResource
    {
        return new UserResource($this->handle($request->validated()));
    }
}
