<?php

namespace App\Models;

use App\Enums\Role;
use Carbon\Carbon;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Account extends Authenticatable implements MustVerifyEmail, OAuthenticatable
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
        'socialite_provider',
        'socialite_provider_id',
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

    public function hasVerifiedEmail(): bool
    {
        if (!config('spectacular.verification')) {
            return true;
        }

        return $this->email_verified_at !== null;
    }

    public function sendEmailVerificationNotification(): void
    {
        if (!config('spectacular.verification')) {
            return;
        }

        $this->notify(new VerifyEmail());
    }

    public function getAuthIdentifierName()
    {
        return 'sqid';
    }

    /* Relations */

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function collaborations()
    {
        return $this->hasMany(Collaboration::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'collaborations')
            ->withPivot('role', 'read_at')
            ->withTimestamps();
    }

    /* Helpers */

    public function markAsRead(Project $project, ?Carbon $timestamp = null)
    {
        if (!$timestamp) {
            $timestamp = now();
        }

        $this->projects()->updateExistingPivot($project, [
            'read_at' => $timestamp,
        ]);
    }

    public function canView(Model $model, ?string $via = null)
    {
        if ($model instanceof Project) {
            return $model->collaborations
                ->where('account_id', $this->getKey())
                ->where('project_id', $model->getKey())
                ->isNotEmpty();
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

            return $model->collaborations
                ->where('account_id', $this->getKey())
                ->where('project_id', $model->getKey())
                ->whereIn('role', [Role::EDITOR, Role::OWNER], true)
                ->isNotEmpty();
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
            return $model->collaborations
                ->where('account_id', $this->getKey())
                ->where('project_id', $model->getKey())
                ->where('role', Role::OWNER)
                ->isNotEmpty();
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
