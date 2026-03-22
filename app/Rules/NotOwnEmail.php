<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Account;

class NotOwnEmail implements ValidationRule
{
    public function __construct(private Account $account)
    {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strtolower($value) === strtolower($this->account->email)) {
            $fail('You cannot use your own email address.');
        }
    }
}
