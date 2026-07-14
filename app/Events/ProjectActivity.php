<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectActivity implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Project $project) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('projects.' . $this->project->sqid)];
    }

    public function broadcastWith(): array
    {
        return [
            'account_id' => auth()->check() ? auth()->user()->sqid : null,
        ];
    }
}
