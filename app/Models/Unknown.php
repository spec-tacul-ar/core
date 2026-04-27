<?php

namespace App\Models;

use App\Casts\AsSqid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unknown extends Model
{
    use HasFactory;
    use Traits\HasSqid;
    use Traits\Revisionable;

    protected $fillable = [
        'requirement_id',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'requirement_sqid' => AsSqid::class,
        ];
    }

    /* Relations */

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
