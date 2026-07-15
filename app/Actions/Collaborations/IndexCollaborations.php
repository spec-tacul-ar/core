<?php

namespace App\Actions\Collaborations;

use App\Http\Resources\CollaborationResource;
use App\Models\Collaboration;
use App\Models\Project;
use App\Rules\Authorised;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class IndexCollaborations
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->get('collaborations', static::class)
            ->middleware('sqids:project_id');
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', new Authorised('view', Project::class)],
        ];
    }

    public function handle(array $validated)
    {
        return Collaboration::query()
            ->where('project_id', $validated['project_id'])
            ->with('account')
            ->get();
    }

    public function asController(ActionRequest $request): ResourceCollection
    {
        return CollaborationResource::collection($this->handle($request->validated()));
    }
}
