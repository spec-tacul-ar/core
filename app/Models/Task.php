<?php

namespace App\Models;

use App\Casts\AsSqid;
use App\Models\Scopes\WeightedScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as HasBelongsToThrough;

class Task extends Model
{
    use HasFactory;
    use Traits\BroadcastsActivity;
    use Traits\HasSqid;
    use Traits\Revisionable;
    use HasBelongsToThrough;

    protected $casts = [
        'is_complete' => 'boolean',
        'requirement_sqid' => AsSqid::class,
    ];

    protected $fillable = [
        'is_complete',
        'name',
        'requirement_id',
        'weight',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new WeightedScope());

        static::saved(fn($task) => $task->requirement->trackActivity());
        static::deleted(fn($task) => $task->requirement->trackActivity());
    }

    /* Relations */

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function project(): BelongsToThrough
    {
        return $this->belongsToThrough(Project::class, [Feature::class, Requirement::class]);
    }
}
