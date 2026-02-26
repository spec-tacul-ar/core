<?php

namespace Spectacular\Core\Actions\Unknowns;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\UnknownResource;
use Spectacular\Core\Models\Unknown;

class UpdateUnknown
{
    use AsAction;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function handle(Unknown $unknown, array $validated): Unknown
    {
        $unknown->update($validated);

        return $unknown;
    }

    public function asController(ActionRequest $request, Unknown $unknown): UnknownResource
    {
        return new UnknownResource($this->handle($unknown, $request->validated()));
    }
}
