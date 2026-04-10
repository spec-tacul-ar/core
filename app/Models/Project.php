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
use Illuminate\Support\Str;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Project extends Model
{
    use HasFactory;
    use HasRelationships;
    use Traits\Revisionable;

    protected $fillable = [
        'description',
        'name',
    ];

    protected static function booted(): void
    {
        static::creating(function ($project) {
            if (!$project->uuid) {
                $project->uuid = (string) Str::uuid();
            }
        });

        static::deleting(function ($project) {
            $project->comments->each->delete();
            $project->contributors->each->delete();
            $project->features()->withTrashed()->get()->each->forceDelete();
            $project->invitations->each->delete();
            $project->users()->withTrashed()->get()->each->forceDelete();
        });
    }

    /* Relations */

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'contributors');
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
            fn ($query) => $query->where('account_id', auth()->id())
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

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /* Scopes */

    public function scopeWithMe($query)
    {
        $query->with(['contributors' => function ($query) {
            $query->where('account_id', auth()->id());
        }]);
    }

    /* Helpers */

    public function loadAll(): static
    {
        return $this->load([
            'contributors.account',
            'features',
            'features.requirements',
            'features.requirements.assignments.user',
            'features.requirements.tasks',
            'features.requirements.unknowns',
            'readmark',
            'users',
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
        // TODO Lock this validation down and make sure the writing logic handles nullables.
        // TODO Wrap it in a DB transition.

        $validated = Validator::make($data, [
            'uuid' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'users' => ['required', 'array'],
            'users.*.id' => ['required', 'integer', 'distinct'],
            'users.*.name' => ['required', 'string', 'max:255'],
            'users.*.summary' => ['nullable', 'string'],
            'users.*.weight' => ['nullable', 'integer', 'min:0', 'max:255'],

            'features' => ['required', 'array'],
            'features.*.name' => ['required', 'string', 'max:255'],
            'features.*.description' => ['nullable', 'string'],
            'features.*.weight' => ['nullable', 'integer', 'min:0', 'max:255'],
            'features.*.requirements' => ['required', 'array'],

            'features.*.requirements.*.name' => ['required', 'string', 'max:255'],
            'features.*.requirements.*.description' => ['nullable', 'string'],
            'features.*.requirements.*.blocked_reason' => ['nullable', 'string', 'max:255'],
            'features.*.requirements.*.source' => ['nullable', 'string', 'max:255'],
            'features.*.requirements.*.reference' => ['nullable', 'integer', 'min:1'],
            'features.*.requirements.*.weight' => ['nullable', 'integer', 'min:0', 'max:255'],

            'features.*.requirements.*.tasks' => ['nullable', 'array'],
            'features.*.requirements.*.tasks.*.name' => ['required', 'string', 'max:255'],
            'features.*.requirements.*.tasks.*.estimate' => ['nullable', 'numeric', new QuarterHourRule()],
            'features.*.requirements.*.tasks.*.is_complete' => ['nullable', 'boolean'],
            'features.*.requirements.*.tasks.*.weight' => ['nullable', 'integer', 'min:0', 'max:255'],

            'features.*.requirements.*.unknowns' => ['nullable', 'array'],
            'features.*.requirements.*.unknowns.*.name' => ['required', 'string', 'max:255'],

            'features.*.requirements.*.user_ids' => ['nullable', 'array'],
            'features.*.requirements.*.user_ids.*' => ['required', 'integer'],
        ])->validate();

        $project = Project::firstOrNew(['uuid' => $validated['uuid']]);

        DB::beginTransaction();

        if ($project->exists) {
            $project->delete();
        }

        $project->fill([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'uuid' => $validated['uuid'],
        ]);

        $project->save();

        $users = collect($validated['users'])
            ->keyBy('id')
            ->map(fn($user) => $project->users()->create([
                'name' => $user['name'],
                'summary' => $user['summary'] ?? null,
                'weight' => $user['weight'] ?? null,
            ]));

        foreach ($validated['features'] as $feature_data) {
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

                foreach ($requirement_data['tasks'] as $task_data) {
                    $requirement_model->tasks()->create([
                        'name' => $task_data['name'],
                        'estimate' => $task_data['estimate'] ?? null,
                        'is_complete' => $task_data['is_complete'] ?? false,
                        'weight' => $task_data['weight'] ?? null,
                    ]);
                }

                foreach ($requirement_data['unknowns'] as $unknown_data) {
                    $requirement_model->unknowns()->create([
                        'name' => $unknown_data['name'],
                    ]);
                }

                foreach ($requirement_data['user_ids'] as $user_id) {
                    $requirement_model->users()->attach($users[$user_id]);
                }
            }
        }

        DB::commit();

        return $project;
    }
}
