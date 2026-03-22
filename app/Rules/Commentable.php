<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

class Commentable implements ValidationRule, ValidatorAwareRule
{
    protected $validator;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Load model

        $type = Arr::get($this->validator->getData(), 'commentable_type');
        $class = Relation::getMorphedModel($type);

        $model = $class::find($value);

        if (!$model) {
            $fail('The :attribute cannot be found.');
            return;
        }

        // Check authorised

        if (!auth()->user()->can('update', $model)) {
            $fail('You are not authorized to view this :attribute.');
            return;
        }

        // Check related

        $project_id = Arr::get($this->validator->getData(), 'project_id');

        if ($type === 'feature' && $model->project_id !== $project_id) {
            $fail('This feature does not belong to the project.');
            return;
        }

        if ($type === 'requirement' && !$model->whereRelation('feature.project', 'id', $project_id)->exists()) {
            $fail('This requirement does not belong to the project.');
            return;
        }
    }

    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }
}
