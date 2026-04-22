<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Models\Account;

class CreateNewAccount implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered account.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): Account
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:250'],
            'email' => [
                'required',
                'string',
                'email',
                'max:250',
                Rule::unique(Account::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return Account::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
