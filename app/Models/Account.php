<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;
use Laravel\Sanctum\HasApiTokens;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use App\Enums\Role;

class Account extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRelationships;
    use MustVerifyEmailTrait;
    use Notifiable;
    use Traits\HasSqid;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Account $account) {
            $account->projects()
                ->wherePivot('role', Role::OWNER)
                ->get()
                ->each->delete();

            $account->projects()->detach();
            $account->comments()->delete();
            $account->invitations()->delete();
            $account->readmarks()->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function findByEmail(string $email)
    {
        return static::firstWhere('email', $email);
    }

    public function markEmailAsVerified()
    {
        $verified = parent::markEmailAsVerified();

        if ($verified) {
            event(new Verified($this));
        }

        return $verified;
    }

    /* Relations */

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function contributors()
    {
        return $this->hasMany(Contributor::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'contributors')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedProjects()
    {
        return $this->belongsToMany(Project::class, 'contributors')
            ->withPivot('role')
            ->withTimestamps()
            ->wherePivot('role', Role::OWNER);
    }

    public function readmarks()
    {
        return $this->hasMany(Readmark::class);
    }

    /* Helpers */

    public function markAsRead(Project $project, ?Carbon $timestamp = null)
    {
        if (!$timestamp) {
            $timestamp = now();
        }

        $readmark = $this->readmarks()->whereBelongsTo($project)->first();

        if (!$readmark) {
            $readmark = $this->readmarks()->make()->forProject($project);
        }

        $readmark->updated_at = $timestamp;
        $readmark->save();

        return $readmark;
    }

    public function canView(Model $model, ?string $via = null)
    {
        if ($model instanceof Project) {
            return Contributor::query()
                ->whereBelongsTo($this)
                ->whereBelongsTo($model)
                ->exists();
        }

        if ($via === null) {
            throw new InvalidArgumentException();
        }

        return $this->projects()
            ->whereRelation($via, $model->getQualifiedKeyName(), $model->id)
            ->exists();
    }

    public function canEdit(Model $model, ?string $via = null)
    {
        if ($model instanceof Project) {
            if ($model->isArchived()) {
                return false;
            }

            return Contributor::query()
                ->whereBelongsTo($this)
                ->whereBelongsTo($model)
                ->whereIn('role', [Role::EDITOR, Role::OWNER])
                ->exists();
        }

        if ($via === null) {
            throw new InvalidArgumentException();
        }

        return $this->projects()
            ->whereNull('projects.archived_at')
            ->wherePivotIn('role', [Role::EDITOR, Role::OWNER])
            ->whereRelation($via, $model->getQualifiedKeyName(), $model->id)
            ->exists();
    }

    public function owns(Model $model, ?string $via = null)
    {
        if ($model instanceof Project) {
            return Contributor::query()
                ->whereBelongsTo($this)
                ->whereBelongsTo($model)
                ->where('role', Role::OWNER)
                ->exists();
        }

        if ($via === null) {
            throw new InvalidArgumentException();
        }

        return $this->projects()
            ->wherePivot('role', Role::OWNER)
            ->whereRelation($via, $model->getQualifiedKeyName(), $model->id)
            ->exists();
    }
}
