<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class SharesRelation implements DataAwareRule, ValidationRule
{
    public function __construct(
        protected string $class,
        protected string $other_field,
        protected string $relation,
    ) {
        //
    }

    protected $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $related = $this->class::query()
            ->whereKey($value)
            ->whereHas($this->relation, fn($query) => $query->whereKey($this->data[$this->other_field]))
            ->exists();

        if (!$related) {
            $relation_name = str($this->relation)->afterLast('.');

            $fail('The :attribute must belong to the ' . $relation_name . '.');
        }
    }
}
