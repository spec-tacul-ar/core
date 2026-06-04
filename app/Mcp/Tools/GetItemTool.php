<?php

namespace App\Mcp\Tools;

use App\Models\Actor;
use App\Models\Assignment;
use App\Models\Feature;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Task;
use App\Models\Unknown;
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
#[Name('GetItemTool')]
#[Description('Returns the specified entity, optionally including its history.')]
class GetItemTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'id' => ['required', 'string'],
            'type' => ['required', 'string', 'in:actor,assignment,feature,project,requirement,task,unknown'],
            'since' => ['nullable', 'date'],
        ]);

        $model = match ($validated['type']) {
            'actor' => new Actor(),
            'assignment' => new Assignment(),
            'feature' => new Feature(),
            'project' => new Project(),
            'requirement' => new Requirement(),
            'task' => new Task(),
            'unknown' => new Unknown(),
        };

        $item = $model->resolveSoftDeletableRouteBinding($validated['id']);

        if (!$item || !$request->user()->can('view', $item)) {
            return Response::error('This ' . $validated['type'] . ' cannot be found.');
        }

        $data = $item->obfuscateIdentifiers($item->toArray());

        if (!empty($validated['since'])) {
            $since = Carbon::parse($validated['since']);

            $item->loadHistory();

            $history = $item->history
                ->where('timestamp', '>', $validated['since'])
                ->map(fn($revision) => [
                    'timestamp' => $revision['timestamp'],
                    'data' => $item->obfuscateIdentifiers($revision['data']),
                ])
                ->values();

            $data['history'] = $history;
        }

        return Response::structured($data);
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

            'type' => $schema->string()
                ->description("The type of item to return. This has to be one of the following: actor, assignment, feature, project, requirement, task, unknown")
                ->required(),

            'since' => $schema->string()
                ->description("The ISO 8601 timestamp from which to return the item's change history. Leave blank when no history is needed.")
                ->nullable(),

        ];
    }
}
