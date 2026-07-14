<?php

use App\Models\Account;
use App\Models\Project;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('projects.{project}', fn (Account $user, Project $project) => $user->can('view', $project));
