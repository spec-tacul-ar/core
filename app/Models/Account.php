<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
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

    public static function findBySocial(string $provider, string $provider_id)
    {
        return static::firstWhere([
            'socialite_provider' => $provider,
            'socialite_provider_id' => $provider_id,
        ]);
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
        return $this->belongsToMany(Project::class, 'contributors');
    }

    public function ownedProjects()
    {
        return $this->belongsToMany(Project::class, 'contributors')->wherePivot('role', Role::OWNER);
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

    public function getLimits(?string $type = null)
    {
        $limits = [
            'projects' => 5,
            'features' => 100,
            'users' => 25,
            'requirements' => 250,
            'tasks' => 1000,
            'unknowns' => 1000,
        ];

        if ($type) {
            return $limits[$type];
        }

        return $limits;
    }

    public function getUsage(?string $type = null)
    {
        $usage = [
            'projects' => fn () => $this->ownedProjects()->count(),
            'features' => fn () => $this->hasManyDeepFromRelationsWithConstraints(
                [$this, 'ownedProjects'],
                [new Project(), 'features'],
            )->count(),
            'users' => fn () => $this->hasManyDeepFromRelationsWithConstraints(
                [$this, 'ownedProjects'],
                [new Project(), 'users'],
            )->count(),
            'requirements' => fn () => $this->hasManyDeepFromRelationsWithConstraints(
                [$this, 'ownedProjects'],
                [new Project(), 'features'],
                [new Feature(), 'requirements'],
            )->count(),
            'tasks' => fn () => $this->hasManyDeepFromRelationsWithConstraints(
                [$this, 'ownedProjects'],
                [new Project(), 'features'],
                [new Feature(), 'requirements'],
                [new Requirement(), 'tasks'],
            )->count(),
            'unknowns' => fn () => $this->hasManyDeepFromRelationsWithConstraints(
                [$this, 'ownedProjects'],
                [new Project(), 'features'],
                [new Feature(), 'requirements'],
                [new Requirement(), 'unknowns'],
            )->count(),
        ];

        if ($type) {
            return $usage[$type]();
        }

        return array_map(fn ($resolver) => $resolver(), $usage);
    }

    public function hasReachedLimit(string $type): bool
    {
        return $this->getUsage($type) >= $this->getLimits($type);
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
