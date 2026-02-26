<?php

namespace Spectacular\Core\Actions\Unknowns;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\UnknownResource;
use Spectacular\Core\Models\Unknown;

class StoreUnknown
{
    use AsAction;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'requirement_id' => ['required', 'integer'],
        ];
    }

    public function handle(array $validated): Unknown
    {
        return Unknown::create($validated);
    }

    public function asController(ActionRequest $request): UnknownResource
    {
        return new UnknownResource($this->handle($request->validated()));
    }
}
