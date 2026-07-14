<?php

namespace App\Mcp\Tools;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Name('GetChangesTool')]
#[Description('Returns entities that have changed since the given timestamp.')]
class GetChangesTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        // TODO Explicit error messages for AI
        $validated = $request->validate([
            'id' => ['required', 'string'],
            'since' => ['required', 'date', 'before_or_equal:now'],
        ]);

        $project = (new Project())->resolveRouteBinding($validated['id']);
        $since = Carbon::parse($validated['since']);

        if (!$project || !$request->user()->can('view', $project)) {
            return Response::error('Project not found.');
        }

        if ($since->isAfter($project->activity_at)) {
            return Response::text('The project has no changes since the provided timestamp.');
        }

        $relations = ['actors', 'assignments', 'features', 'requirements', 'tasks', 'unknowns'];

        $changes = [];

        foreach ($relations as $relation) {
            $changes[$relation] = $project->{$relation}()
                ->withTrashed()
                ->withHistory()
                ->where($relation . '.updated_at', '>', $since)
                ->get()
                ->map(fn($model) => ['id' => $model->sqid, ...$model->getChangesSince($since)])
                ->filter()
                ->values();
        }

        $changes['project'] = ['id' => $project->sqid, ...$project->getChangesSince($since)];

        return Response::structured($changes);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [

            'id' => $schema->string()
                ->description('The primary identifier for the specification.')
                ->required(),

            'since' => $schema->string()
                ->description("Return records that changed after this ISO 8601 timestamp, usually the last generated_at value.")
                ->required(),

        ];
    }
}
