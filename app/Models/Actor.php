<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Actor extends Model
{
    use HasFactory;
    use Traits\Revisionable;

    protected $fillable = [
        'name',
        'project_id',
        'summary',
        'weight',
    ];

    protected static function booted(): void
    {
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
