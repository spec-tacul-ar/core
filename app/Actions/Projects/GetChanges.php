<?php

namespace App\Actions\Projects;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GetChanges
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('view', $request->route('project'));
    }

    public static function routes(Router $router): void
    {
        $router->get('projects/{project}/changes', static::class);
    }

    public function rules(): array
    {
        return [
            'since' => ['required', 'date', 'before_or_equal:now'],
        ];
    }

    public function handle(Project $project, string $since): array
    {
        $since = Carbon::parse($since);

        $changes = [];

        if (!$project->activity_at || !$project->activity_at->isAfter($since)) {
            return $changes;
        }

        $relations = ['actors', 'assignments', 'features', 'requirements', 'tasks', 'unknowns'];

        foreach ($relations as $relation) {
            $changes[$relation] = $project->{$relation}()
                ->withTrashed()
                ->withHistory()
                ->where($relation . '.updated_at', '>', $since)
                ->get()
                ->map(fn($model) => ['id' => $model->sqid, ...$model->getChangesSince($since)])
                ->filter()
                ->values()
                ->all();
        }

        if ($project->updated_at->isAfter($since)) {
            $changes['project'] = ['id' => $project->sqid, ...$project->getChangesSince($since)];
        }

        return array_filter($changes);
    }

    public function asController(ActionRequest $request, Project $project): JsonResource
    {
        return new JsonResource($this->handle($project, $request->validated('since')));
    }
}
