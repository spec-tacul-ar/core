<?php

namespace App\Models;

use App\Casts\AsSqid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unknown extends Model
{
    use HasFactory;
    use Traits\BroadcastsActivity;
    use Traits\HasSqid;
    use Traits\Revisionable;

    protected function casts(): array
    {
        return [
            'requirement_sqid' => AsSqid::class,
        ];
    }

    protected $fillable = [
        'requirement_id',
        'name',
    ];

    protected static function booted(): void
    {
        static::saved(fn($unknown) => $unknown->requirement->trackActivity());
        static::deleted(fn($unknown) => $unknown->requirement->trackActivity());
    }

    /* Relations */

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
