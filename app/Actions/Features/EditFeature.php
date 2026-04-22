<?php

namespace App\Actions\Features;

use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use App\Models\Project;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\ValidationRules\Rules\Authorized;

class EditFeature
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('update', $request->route('feature'));
    }

    public static function routes(Router $router): void
    {
        $router->post('features/{feature}/edit', static::class);
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:10000'],
            'name' => ['required', 'string', 'max:250'],
            'project_id' => ['sometimes', 'bail', 'required', 'integer', new Authorized('update', Project::class)],
            'weight' => ['nullable', 'integer', 'between:0,250'],
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
