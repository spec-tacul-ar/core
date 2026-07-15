<?php

namespace App\Models;

use App\Casts\AsSqid;
use App\Models\Scopes\WeightedScope;
use App\Models\Traits\BroadcastsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Actor extends Model
{
    use BroadcastsActivity;
    use HasFactory;
    use Traits\HasSqid;
    use Traits\Revisionable;

    protected function casts(): array
    {
        return [
            'project_sqid' => AsSqid::class,
        ];
    }

    protected $fillable = [
        'name',
        'project_id',
        'summary',
        'weight',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new WeightedScope());

        static::saved(fn($actor) => $actor->project->trackActivity());
        static::deleted(fn($actor) => $actor->project->trackActivity());

        static::deleting(function ($actor) {
            $actor->assignments->each->delete();
        });

        static::forceDeleting(function ($actor) {
            $actor->assignments()->withTrashed()->forceDelete();
        });
    }

    /* Relations */

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
