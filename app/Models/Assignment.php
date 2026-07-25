<?php

namespace App\Models;

use App\Casts\AsSqid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as HasBelongsToThrough;

class Assignment extends Model
{
    use Traits\BroadcastsActivity;
    use Traits\HasSqid;
    use Traits\Revisionable;
    use HasBelongsToThrough;

    protected function casts(): array
    {
        return [
            'actor_sqid' => AsSqid::class,
            'requirement_sqid' => AsSqid::class,
        ];
    }

    protected $fillable = [
        'actor_id',
    ];

    protected static function booted(): void
    {
        static::saved(fn($assignment) => $assignment->requirement->trackActivity());
        static::deleted(fn($assignment) => $assignment->requirement->trackActivity());
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function project(): BelongsToThrough
    {
        return $this->belongsToThrough(Project::class, [Feature::class, Requirement::class]);
    }
}
