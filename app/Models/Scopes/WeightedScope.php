<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class WeightedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder
            ->orderBy($model->qualifyColumn('weight'))
            ->orderBy($model->qualifyColumn($model->getKeyName()));
    }
}
