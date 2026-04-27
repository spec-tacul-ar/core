<?php

namespace App\Models;

use App\Casts\AsSqid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Requirement extends Model
{
    use HasFactory;
    use Traits\HasSqid;
    use Traits\Revisionable;

    protected $casts = [
        'actor_id' => 'integer',
        'actor_sqid' => AsSqid::class,
        'feature_sqid' => AsSqid::class,
    ];

    protected $fillable = [
        'blocked_reason',
        'description',
        'name',
        'feature_id',
        'source',
        'actor_id',
        'weight',
    ];

    protected static function booted(): void
    {
        static::created(function ($requirement) {
            // We have to do it like this to prevent duplicates from race conditions.
            DB::transaction(function () use ($requirement) {
                $project = $requirement->feature->project()->lockForUpdate()->first();

                $next_requirement_reference = $project->next_requirement_reference;

                $project->incrementQuietly('next_requirement_reference');

                $requirement->reference = $next_requirement_reference;
                $requirement->save();
            });
        });

        static::deleting(function ($requirement) {
            $requirement->assignments->each->delete();
            $requirement->unknowns->each->delete();
            $requirement->tasks->each->delete();
        });

        static::forceDeleting(function ($requirement) {
            $requirement->assignments()->withTrashed()->forceDelete();
            $requirement->unknowns()->withTrashed()->forceDelete();
            $requirement->tasks()->withTrashed()->forceDelete();
        });
    }

    /* Relations */

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function unknowns(): HasMany
    {
        return $this->hasMany(Unknown::class);
    }

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class, Assignment::class);
    }

    /* Attributes */

    public function isBlocked(): Attribute
    {
        return new Attribute(fn() => !!$this->blocked_reason);
    }

    public function isComplete(): Attribute
    {
        return new Attribute(fn() => $this->tasks->isNotEmpty() && $this->tasks->every(fn($task) => $task->is_complete));
    }

    public function name(): Attribute
    {
        return new Attribute(set: fn($value) => rtrim($value, '.?!'));
    }

    public function title(): Attribute
    {
        return new Attribute(function () {
            $actors = $this->actors->isNotEmpty() ? $this->actors->pluck('name') : collect(['Users']);

            $last_actor = $actors->pop();

            return (!$actors->isEmpty() ? $actors->implode(', ') . ' and ' : '') . $last_actor . ' can ' . $this->name;
        });
    }

    public function tasksEstimate(): Attribute
    {
        return new Attribute(fn() => $this->tasks->sum('estimate'));
    }
}
