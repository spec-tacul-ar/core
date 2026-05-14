<?php

namespace App\Models;

use App\Enums\Role;
use App\Rules\QuarterHour as QuarterHourRule;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Project extends Model
{
    use HasFactory;
    use HasRelationships;
    use Traits\HasSqid;
    use Traits\Revisionable;

    protected $fillable = [
        'description',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function ($project) {
            $project->comments->each->delete();
            $project->contributors->each->delete();
            $project->features()->withTrashed()->get()->each->forceDelete();
            $project->invitations->each->delete();
            $project->actors()->withTrashed()->get()->each->forceDelete();
        });
    }

    /* Relations */

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'contributors')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function contributors()
    {
        return $this->hasMany(Contributor::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(Feature::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function me()
    {
        return $this->hasOne(Contributor::class)->ofMany(
            ['id' => 'min'],
            fn($query) => $query->where('account_id', auth()->id()),
        );
    }

    public function readmark()
    {
        return $this->hasOne(Readmark::class)->where('account_id', auth()->id());
    }

    public function requirements(): HasManyThrough
    {
        return $this->hasManyThrough(Requirement::class, Feature::class);
    }

    public function unknowns(): HasManyDeep
    {
        return $this->hasManyDeep(Unknown::class, [Feature::class, Requirement::class]);
    }

    public function tasks(): HasManyDeep
    {
        return $this->hasManyDeep(Task::class, [Feature::class, Requirement::class]);
    }

    public function actors(): HasMany
    {
        return $this->hasMany(Actor::class);
    }

    /* Scopes */

    public function scopeWithMe($query)
    {
        $query->with(['contributors' => function ($query) {
            $query->where('account_id', auth()->id());
        }]);
    }

    public function scopeOwnedBy($query, Account $account)
    {
        return $query->whereHas('contributors', function ($query) use ($account) {
            return $query->whereBelongsTo($account)->where('role', Role::OWNER);
        });
    }

    public function scopeArchived($query, bool $archived = true)
    {
        return $archived
            ? $query->whereNotNull('archived_at')
            : $query->whereNull('archived_at');
    }

    /* Helpers */

    public function loadAll(): static
    {
        return $this->load([
            'contributors.account',
            'features',
            'features.requirements',
            'features.requirements.assignments.actor',
            'features.requirements.tasks',
            'features.requirements.unknowns',
            'readmark',
            'actors',
        ]);
    }

    public function featuresEstimate(): Attribute
    {
        return new Attribute(fn() => $this->features->sum('requirements_estimate'));
    }

    public function totalEstimate(): Attribute
    {
        return new Attribute(fn() => $this->features_estimate);
    }

    public function addContributor(Account $account, Role $role): static
    {
        $this->contributors()->make(['role' => $role])->account()->associate($account)->save();

        return $this;
    }

    public static function import(array $data): static
    {
        // Note: This is only good for importing the demo project at the moment.

        $data = Validator::make($data, [
            'id' => ['nullable', 'uuid'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'actors' => ['required', 'array'],
            'actors.*.id' => ['required'],
            'actors.*.name' => ['required', 'string'],
            'actors.*.summary' => ['nullable', 'string'],
            'actors.*.weight' => ['nullable', 'integer'],
            'features' => ['required', 'array'],
            'features.*.name' => ['required', 'string'],
            'features.*.description' => ['nullable', 'string'],
            'features.*.weight' => ['nullable', 'integer'],
            'features.*.requirements' => ['required', 'array'],
            'features.*.requirements.*.name' => ['nullable', 'string'],
            'features.*.requirements.*.description' => ['nullable', 'string'],
            'features.*.requirements.*.blocked_reason' => ['nullable', 'string'],
            'features.*.requirements.*.source' => ['nullable', 'string'],
            'features.*.requirements.*.reference' => ['nullable'],
            'features.*.requirements.*.weight' => ['nullable', 'integer'],
            'features.*.requirements.*.tasks' => ['sometimes', 'array'],
            'features.*.requirements.*.tasks.*.name' => ['required', 'string'],
            'features.*.requirements.*.tasks.*.estimate' => ['nullable', new QuarterHourRule()],
            'features.*.requirements.*.tasks.*.is_complete' => ['boolean'],
            'features.*.requirements.*.tasks.*.weight' => ['nullable', 'integer'],
            'features.*.requirements.*.unknowns' => ['sometimes', 'array'],
            'features.*.requirements.*.unknowns.*.name' => ['required', 'string'],
            'features.*.requirements.*.actor_ids' => ['sometimes', 'array'],
        ])->validate();

        return DB::transaction(function () use ($data) {
            $project = new Project([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            $project->save();

            $actors = collect($data['actors'])
                ->keyBy('id')
                ->map(fn($actor) => $project->actors()->create([
                    'name' => $actor['name'],
                    'summary' => $actor['summary'] ?? null,
                    'weight' => $actor['weight'] ?? null,
                ]));

            foreach ($data['features'] as $feature_data) {
                $feature_model = $project->features()->create([
                    'name' => $feature_data['name'],
                    'description' => $feature_data['description'] ?? null,
                    'weight' => $feature_data['weight'] ?? null,
                ]);

                foreach ($feature_data['requirements'] as $requirement_data) {
                    $requirement_model = $feature_model->requirements()->create([
                        'name' => $requirement_data['name'] ?? null,
                        'description' => $requirement_data['description'] ?? null,
                        'blocked_reason' => $requirement_data['blocked_reason'] ?? null,
                        'source' => $requirement_data['source'] ?? null,
                        'reference' => $requirement_data['reference'] ?? null,
                        'weight' => $requirement_data['weight'] ?? null,
                    ]);

                    foreach ($requirement_data['tasks'] ?? [] as $task_data) {
                        $requirement_model->tasks()->create([
                            'name' => $task_data['name'],
                            'estimate' => $task_data['estimate'] ?? null,
                            'is_complete' => $task_data['is_complete'] ?? false,
                            'weight' => $task_data['weight'] ?? null,
                        ]);
                    }

                    foreach ($requirement_data['unknowns'] ?? [] as $unknown_data) {
                        $requirement_model->unknowns()->create([
                            'name' => $unknown_data['name'],
                        ]);
                    }

                    foreach ($requirement_data['actor_ids'] ?? [] as $actor_id) {
                        $requirement_model->assignments()->create([
                            'actor_id' => $actors[$actor_id]->id,
                        ]);
                    }
                }
            }

            return $project;
        });
    }

    /* Attributes */

    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn() => str($this->name)->slug()->toString() ?: 'project',
        );
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn() => url(config('spectacular.path') . '/projects/' . $this->getRouteKey()),
        );
    }
}
