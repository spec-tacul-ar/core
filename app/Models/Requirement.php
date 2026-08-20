<?php

namespace App\Models;

use App\Casts\AsSqid;
use App\Models\Scopes\WeightedScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as HasBelongsToThrough;

class Requirement extends Model
{
    use HasFactory;
    use Traits\BroadcastsActivity;
    use Traits\HasSqid;
    use Traits\Revisionable;
    use Traits\TracksActivity;
    use HasBelongsToThrough;

    protected $appends = ['title'];

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
        static::addGlobalScope(new WeightedScope());

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

        static::saved(fn($requirement) => $requirement->trackActivity());
        static::deleted(function ($requirement) {
            if (! $requirement->isForceDeleting()) {
                $requirement->trackActivity();
            }
        });

        static::deleting(function ($requirement) {
            $requirement->comments()->delete();
            $requirement->assignments->each->delete();
            $requirement->unknowns->each->delete();
            $requirement->tasks->each->delete();
        });

        static::forceDeleting(function ($requirement) {
            $requirement->comments()->delete();
            $requirement->assignments()->withTrashed()->forceDelete();
            $requirement->unknowns()->withTrashed()->forceDelete();
            $requirement->tasks()->withTrashed()->forceDelete();
        });
    }

    protected function handleActivity()
    {
        $this->feature->trackActivity();
    }

    /* Relations */

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class, Assignment::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function project(): BelongsToThrough
    {
        return $this->belongsToThrough(Project::class, Feature::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->chaperone();
    }

    public function unknowns(): HasMany
    {
        return $this->hasMany(Unknown::class)->chaperone();
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
            $locale = $this->feature->project->locale;

            $actors = $this->actors->isNotEmpty() ? $this->actors->pluck('name') : collect([__('Users', locale: $locale)]);

            // We will try and use PHP 8.5's IntlListFormatter when available.
            // This has better support for languages that change the conjunction based on vowel sounds.
            if (class_exists(\IntlListFormatter::class)) {
                try {
                    $roles = (new \IntlListFormatter($locale))->format($actors->all());

                    if ($roles !== false) {
                        return $roles . ' ' . __('can', locale: $locale) . ' ' . $this->name;
                    }
                } catch (\IntlException) {
                    // Fall back to the simple method below.
                }
            }

            $last_actor = $actors->pop();

            return (!$actors->isEmpty() ? $actors->implode(', ') . ' ' . __('and', locale: $locale) . ' ' : '') . $last_actor . ' ' . __('can', locale: $locale) . ' ' . $this->name;
        });
    }
}
