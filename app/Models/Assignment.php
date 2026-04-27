<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    use Traits\HasSqid;
    use Traits\Revisionable;

    protected $fillable = [
        'actor_id',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
