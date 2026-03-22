<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function html(Project $project): Response
    {
        $project->loadAll();

        return response()->view('export.html', compact('project'));
    }

    public function markdown(Project $project): Response
    {
        $project->loadAll();

        return response()->view('export.markdown', compact('project'))->header('Content-Type', 'text/markdown');
    }

    public function json(Project $project): JsonResponse
    {
        $project->loadAll();

        return response()->json([
            'uuid' => $project->uuid,
            'name' => $project->name,
            'description' => $project->description,
            'users' => $project->users
                ->sortBy('id')
                ->sortBy('weight')
                ->values()
                ->map(fn($user) => [
                    'id' => (int) $user->id,
                    'name' => $user->name,
                    'summary' => $user->summary,
                    'weight' => $user->weight,
                ])
                ->all(),
            'features' => $project->features
                ->sortBy('id')
                ->sortBy('weight')
                ->values()
                ->map(function ($feature) {
                    return [
                        'name' => $feature->name,
                        'description' => $feature->description,
                        'weight' => $feature->weight,
                        'requirements' => $feature->requirements
                            ->sortBy('id')
                            ->sortBy('weight')
                            ->values()
                            ->map(function ($requirement) {
                                return [
                                    'name' => $requirement->name,
                                    'description' => $requirement->description,
                                    'blocked_reason' => $requirement->blocked_reason,
                                    'source' => $requirement->source,
                                    'reference' => $requirement->reference,
                                    'weight' => $requirement->weight,
                                    'tasks' => $requirement->tasks
                                        ->sortBy('id')
                                        ->sortBy('weight')
                                        ->values()
                                        ->map(fn($task) => [
                                            'name' => $task->name,
                                            'estimate' => $task->estimate,
                                            'is_complete' => (bool) $task->is_complete,
                                            'weight' => $task->weight,
                                        ])
                                        ->all(),
                                    'unknowns' => $requirement->unknowns
                                        ->sortBy('id')
                                        ->values()
                                        ->map(fn($unknown) => [
                                            'name' => $unknown->name,
                                        ])
                                        ->all(),
                                    'user_ids' => $requirement->users->pluck('id'),
                                ];
                            })
                            ->all(),
                    ];
                })
                ->all(),
        ])->header('Content-Type', 'application/json');
    }
}
