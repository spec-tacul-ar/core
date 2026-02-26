<?php

namespace Spectacular\Core\Actions;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\FeatureResource;
use Spectacular\Core\Models\Feature;

class FeatureUpdate
{
    use AsAction;

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['nullable', 'integer', 'between:0,255'],
        ];
    }

    public function handle(Feature $feature, array $validated): Feature
    {
        $feature->update($validated);

        return $feature;
    }

    public function asController(ActionRequest $request, Feature $feature): FeatureResource
    {
        return new FeatureResource($this->handle($feature, $request->validated()));
    }
}
