<?php

namespace App\Actions\Projects;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\ProjectResource;
use App\Models\Project;

class BrowseProjects
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->get('projects/browse', static::class);
    }

    public function handle(): LengthAwarePaginator
    {
        $query = config('spectacular.mode') === 'solo' ? Project::query()->doesntHave('contributors') : auth()->user()->projects();

        return $query
            ->withCount([
                'requirements',
                'requirements as blocked_requirements_count' => fn($query) => $query->whereNotNull('blocked_reason'),
                'unknowns',
                'tasks',
                'requirements as requirements_with_tasks_count' => fn($query) => $query->whereHas('tasks'),
                'requirements as requirements_all_tasks_complete_count' => fn($query) => $query
                    ->whereHas('tasks')
                    ->whereDoesntHave('tasks', fn($query) => $query->where('is_complete', false)),
            ])
            ->with(['contributors' => fn($query) => $query->whereBelongsTo(auth()->user())])
            ->orderBy('name', 'asc')
            ->paginate(100)
            ->withQueryString();
    }

    public function asController(): ResourceCollection
    {
        return ProjectResource::collection($this->handle());
    }
}
