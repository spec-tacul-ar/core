<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectItemDeleted implements ShouldBroadcast, ShouldDispatchAfterCommit, ShouldRescue
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Model $model) {}

    public function broadcastOn(): array
    {
        $project = $this->model instanceof Project ? $this->model : $this->model->project;

        return [new PrivateChannel('projects.' . $project->sqid)];
    }

    public function broadcastWith(): array
    {
        return [
            'repository' => str($this->model::class)->classBasename()->plural()->lower(),
            'id' => $this->model->sqid,
        ];
    }
}
