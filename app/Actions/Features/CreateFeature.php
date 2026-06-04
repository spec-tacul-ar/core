<?php

namespace App\Actions\Features;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use App\Models\Project;
use App\Rules\Authorised;

class CreateFeature
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('features', static::class)
            ->middleware('sqids:project_id');
    }

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('create', Feature::class);
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', new Authorised('update', Project::class)],
            'description' => ['nullable', 'string', 'max:10000'],
            'name' => ['required', 'string', 'max:250'],
            'weight' => ['nullable', 'integer', 'between:0,250'],
        ];
    }

    public function handle(array $data): Feature
    {
        return Feature::create($data);
    }

    public function asController(ActionRequest $request): FeatureResource
    {
        $feature = $this->handle($request->validated());

        return new FeatureResource($feature);
    }
}
