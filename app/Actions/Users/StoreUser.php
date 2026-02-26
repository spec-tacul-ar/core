<?php

namespace Spectacular\Core\Actions\Users;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\UserResource;
use Spectacular\Core\Models\User;

class StoreUser
{
    use AsAction;

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer'],
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
