<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Authorised implements ValidationRule
{
    public function __construct(
        protected string $ability,
        protected string $class,
    ) {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $model = $this->class::find($value);

        if (!$model || auth()->user()->cannot('view', $model)) {
            $fail(__('validation.exists', ['attribute' => $attribute]));
            return;
        }

        if ($this->ability === 'view') {
            return;
        }

        if (auth()->user()->cannot($this->ability, $model)) {
            $fail('You are not authorized to use this value.');
        }
    }
}
