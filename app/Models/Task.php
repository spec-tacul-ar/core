<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Traits\Revisionable;

    protected $casts = [
        'is_complete' => 'boolean',
    ];

    protected $fillable = [
        'estimate',
        'is_complete',
        'name',
        'requirement_id',
        'weight',
    ];

    /* Relations */

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    /* Helpers */

    public function complete(): void
    {
        $this->is_complete = true;
        $this->save();
    }

    /* Attributes */

    protected function estimate(): Attribute
    {
        return new Attribute(
            get: fn($value) => $value === null ? null : $value * 0.25,
            set: fn($value) => $value === null ? null : $value / 0.25,
        );
    }
}
