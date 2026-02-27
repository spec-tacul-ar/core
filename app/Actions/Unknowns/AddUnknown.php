<?php

namespace Spectacular\Core\Actions\Unknowns;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\UnknownResource;
use Spectacular\Core\Models\Unknown;

class AddUnknown
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('unknowns/add', static::class);
    }

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
