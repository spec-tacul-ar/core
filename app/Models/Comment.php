<?php

namespace App\Models;

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
