<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectItemSaved implements ShouldBroadcast, ShouldDispatchAfterCommit, ShouldRescue
{
    use Dispatchable;
    use SerializesModels;

    protected string $repository;

    public function __construct(public Model $model)
    {
        $this->repository = str($this->model::class)->classBasename()->plural()->lower();
    }

    public function broadcastOn(): array
    {
        $project = $this->model instanceof Project ? $this->model : $this->model->project;
        return [
            new PrivateChannel('projects.' . $project->sqid),
            new PresenceChannel($this->repository . '.editing.' . $this->model->sqid),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'repository' => $this->repository,
            'data' => $this->model->toResource(),
        ];
    }
}
