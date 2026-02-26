<?php

namespace Spectacular\Core\Actions;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\FeatureResource;
use Spectacular\Core\Models\Feature;

class FeatureStore
{
    use AsAction;

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['nullable', 'integer', 'between:0,255'],
        ];
    }

    public function handle(array $validated): Feature
    {
        return Feature::create($validated);
    }

    public function asController(ActionRequest $request): FeatureResource
    {
        return new FeatureResource($this->handle($request->validated()));
    }
}
