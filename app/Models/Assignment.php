<?php

namespace App\Models;

use App\Casts\AsSqid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    use Traits\HasSqid;
    use Traits\Revisionable;

    protected $fillable = [
        'actor_id',
    ];

    protected function casts(): array
    {
        return [
            'actor_sqid' => AsSqid::class,
            'requirement_sqid' => AsSqid::class,
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
