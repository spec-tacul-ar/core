<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Traits\Revisionable;

    protected $fillable = [
        'name',
        'project_id',
        'summary',
        'weight',
    ];

    protected static function booted(): void
    {
        static::deleting(function ($user) {
            $user->assignments->each->delete();
        });

        static::forceDeleting(function ($user) {
            $user->assignments()->withTrashed()->forceDelete();
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
