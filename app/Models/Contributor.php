<?php

namespace App\Models;

use App\Casts\AsSqid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Role;

class Contributor extends Model
{
    use HasFactory;
    use Traits\HasSqid;

    protected $fillable = [
        'role',
    ];

    protected function casts(): array
    {
        return [
            'account_sqid' => AsSqid::class,
            'project_sqid' => AsSqid::class,
            'role' => Role::class,
        ];
    }

    /* Relations */

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /* Scopes */

    public function scopeOwners($query)
    {
        return $query->where('role', Role::OWNER);
    }
}
