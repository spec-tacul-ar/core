<?php

namespace App\Actions\Projects;

use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ExportProject
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('view', $request->route('project'));
    }

    public static function routes(Router $router): void
    {
        $router
            ->get('export/{project}/{type}', static::class)
            ->name('export.show');
    }

    public function handle(Project $project, string $type): string
    {
        $project->loadAll();

        return match ($type) {
            'html' => $this->generateHtml($project),
            'markdown' => view('export.markdown', compact('project'))->render(),
            'json' => $this->generateJson($project),
            default => abort(404),
        };
    }

    public function asController(ActionRequest $request, Project $project): Response
    {
        $type = $request->route('type');

        $content = $this->handle($project, $type);

        return match ($type) {
            'html' => response($content)->header('Content-Type', 'text/html; charset=UTF-8'),
            'markdown' => response($content)->header('Content-Type', 'text/markdown'),
            'json' => response($content)->header('Content-Type', 'application/json'),
            default => abort(404),
        };
    }

    protected function generateHtml(Project $project): string
    {
        $html = view('export.html', compact('project'))->render();

        if (extension_loaded('tidy') && $html !== '') {
            $options = [
                'indent' => true,
                'indent-spaces' => 4,
                'wrap' => 0,
                'drop-empty-elements' => false,
                'show-body-only' => false,
            ];

            $html = tidy_repair_string($html, $options, 'utf8');
        }

        return $html;
    }

    protected function generateJson(Project $project): string
    {
        return json_encode($this->toExportArray($project), JSON_THROW_ON_ERROR);
    }

    protected function toExportArray(Project $project): array
    {
        return [
            'id' => $project->uuid,
            'name' => $project->name,
            'description' => $project->description,
            'actors' => $project->actors
                ->sortBy('created_at')
                ->sortBy('weight')
                ->values()
                ->map(fn ($actor) => [
                    'id' => $actor->uuid,
                    'name' => $actor->name,
                    'summary' => $actor->summary,
                    'weight' => $actor->weight,
                ])
                ->all(),
            'features' => $project->features
                ->sortBy('created_at')
                ->sortBy('weight')
                ->values()
                ->map(function ($feature) {
                    return [
                        'id' => $feature->uuid,
                        'name' => $feature->name,
                        'description' => $feature->description,
                        'weight' => $feature->weight,
                        'requirements' => $feature->requirements
                            ->sortBy('created_at')
                            ->sortBy('weight')
                            ->values()
                            ->map(function ($requirement) {
                                return [
                                    'id' => $requirement->uuid,
                                    'name' => $requirement->name,
                                    'description' => $requirement->description,
                                    'blocked_reason' => $requirement->blocked_reason,
                                    'source' => $requirement->source,
                                    'reference' => $requirement->reference,
                                    'weight' => $requirement->weight,
                                    'tasks' => $requirement->tasks
                                        ->sortBy('created_at')
                                        ->sortBy('weight')
                                        ->values()
                                        ->map(fn ($task) => [
                                            'id' => $task->uuid,
                                            'name' => $task->name,
                                            'estimate' => $task->estimate,
                                            'is_complete' => (bool) $task->is_complete,
                                            'weight' => $task->weight,
                                        ])
                                        ->all(),
                                    'unknowns' => $requirement->unknowns
                                        ->sortBy('created_at')
                                        ->values()
                                        ->map(fn ($unknown) => [
                                            'id' => $unknown->uuid,
                                            'name' => $unknown->name,
                                        ])
                                        ->all(),
                                    'assignments' => $requirement->assignments->map(fn ($assignment) => [
                                        'id' => $assignment->uuid,
                                        'actor_id' => $assignment->actor_id,
                                    ])
                                    ->all(),
                                ];
                            })
                            ->all(),
                    ];
                })
                ->all(),
        ];
    }
}
