<?php

namespace App\Models;

use App\Casts\AsSqid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    use Traits\HasSqid;

    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'message',
        'project_id',
        'requirement_id',
    ];

    protected function casts(): array
    {
        return [
            'account_sqid' => AsSqid::class,
            'commentable_sqid' => AsSqid::class,
            'project_sqid' => AsSqid::class,
            'requirement_sqid' => AsSqid::class,
        ];
    }

    /* Relations */

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /* Helpers */

    public function authorIs(Account $account): bool
    {
        return $this->account_id === $account->getKey();
    }
}
