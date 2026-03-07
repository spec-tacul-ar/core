<?php

namespace Spectacular\Core\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Traits\Revisionable;

    protected $casts = [
        'is_percentage' => 'boolean',
    ];

    protected $fillable = [
        'description',
        'name',
        'project_id',
        'weight',
    ];

    protected static function booted(): void
    {
        static::deleting(function ($feature) {
            $feature->requirements->each->delete();
        });

        static::forceDeleting(function ($feature) {
            $feature->requirements()->withTrashed()->get()->each->forceDelete();
        });
    }

    /* Relations */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }

    /* Accessors and mutators */

    public function requirementsEstimate(): Attribute
    {
        return new Attribute(fn() => $this->requirements->sum('tasks_estimate'));
    }
}
