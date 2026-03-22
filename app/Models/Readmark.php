<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Readmark extends Model
{
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
