<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unknown extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Traits\Revisionable;

    protected $fillable = [
        'requirement_id',
        'name',
    ];

    /* Relations */

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
