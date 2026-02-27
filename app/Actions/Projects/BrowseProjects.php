<?php

namespace Spectacular\Core\Actions\Projects;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\ProjectResource;
use Spectacular\Core\Models\Project;

class BrowseProjects
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->get('projects/browse', static::class);
    }

    public function handle(): ResourceCollection
    {
        $projects = Project::query()
            ->withCount([
                'requirements',
                'requirements as blocked_requirements_count' => fn ($query) => $query->whereNotNull('blocked_reason'),
                'unknowns',
                'tasks',
                'requirements as requirements_with_tasks_count' => fn ($query) => $query->whereHas('tasks'),
                'requirements as requirements_all_tasks_complete_count' => fn ($query) => $query
                    ->whereHas('tasks')
                    ->whereDoesntHave('tasks', fn ($query) => $query->where('is_complete', false)),
            ])
            ->orderBy('name', 'asc')
            ->get();

        return ProjectResource::collection($projects);
    }

    public function asController(): ResourceCollection
    {
        return $this->handle();
    }
}
