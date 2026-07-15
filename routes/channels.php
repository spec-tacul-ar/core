<?php

use App\Models\Account;
use App\Models\Actor;
use App\Models\Feature;
use App\Models\Project;
use App\Models\Requirement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Str;

Broadcast::channel('projects.{project}', fn (Account $user, Project $project) => $user->can('view', $project));

/* Presence channels */

collect([
    Actor::class,
    Feature::class,
    Project::class,
    Requirement::class,
])->each(function ($class) {
    $singular = str($class)->classBasename()->lower();
    $plural = $singular->plural();

    Broadcast::channel($plural . '.editing.{' . $singular . '}', function (Account $account, string $id) use ($class) {
        $model = (new $class)->resolveRouteBinding($id);

        return $account->can('update', $model) ? ['id' => $account->sqid, 'name' => $account->name] : null;
    });
});
