<?php

namespace App\Mcp\Tools;

use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Name('ListProjectAccountsTool')]
#[Description('Returns a list of accounts assoicated with this project and their IDs. Use this when you need to attribute items in the change history to a specific accounts.')]
class ListProjectAccountsTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'id' => ['required', 'string'],
        ]);

        $project = (new Project())->resolveRouteBinding($validated['id']);

        if (!$project || !$request->user()->can('view', $project)) {
            return Response::error('Project not found.');
        }

        $accounts = $project->collaborators()
            ->orderBy('accounts.name')
            ->orderBy('accounts.id')
            ->get()
            ->map(fn($account) => [
                'name' => $account->name,
                'id' => $account->sqid,
            ])
            ->values();

        return Response::structured([
            'accounts' => $accounts,
        ]);
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
        ];
    }
}
