<?php

namespace App\Actions\Unknowns;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\UnknownResource;
use App\Models\Requirement;
use App\Models\Unknown;
use Spatie\ValidationRules\Rules\Authorized;

class AddUnknown
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('create', Unknown::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('unknowns/add', static::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:250'],
            'requirement_id' => ['required', 'integer', new Authorized('update', Requirement::class)],

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
