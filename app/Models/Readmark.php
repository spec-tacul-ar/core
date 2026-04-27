<?php

namespace App\Models;

use App\Casts\AsSqid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Readmark extends Model
{
    protected function casts(): array
    {
        return [
            'account_sqid' => AsSqid::class,
            'project_sqid' => AsSqid::class,
        ];
    }

    /* Relations */

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /* Helpers */

    public function forProject(Project $project)
    {
        return $this->project()->associate($project);
    }
}
