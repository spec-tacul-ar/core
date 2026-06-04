<?php

namespace App\Actions\Features;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use App\Models\Project;
use App\Rules\Authorised;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class EditFeature
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('update', $request->route('feature'));
    }

    public static function routes(Router $router): void
    {
        $router->post('features/{feature}/edit', static::class)
            ->middleware('sqids:project_id');
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:10000'],
            'name' => ['required', 'string', 'max:250'],
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
        $feature = $this->handle($feature, $request->validated());

        return new FeatureResource($feature);
    }
}
