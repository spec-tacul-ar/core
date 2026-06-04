<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class WithoutHistoryScope implements Scope
{
    protected bool $enabled = true;

    public function enable(): static
    {
        $this->enabled = true;

        return $this;
    }

    public function disable(): static
    {
        $this->enabled = false;

        return $this;
    }

    public function apply(Builder $builder, Model $model): void
    {
        if (! $this->enabled) {
            return;
        }

        if ($builder->getQuery()->columns === null) {
            $builder->select($model->qualifyColumn('*'));
        }

        $builder->addSelect(DB::raw('NULL AS history'));
    }
}
