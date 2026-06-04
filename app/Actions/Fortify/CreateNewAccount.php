<?php

namespace App\Actions\Fortify;

use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

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
            'email' => [
                'required',
                'string',
                'email:filter',
                'max:250',
                Rule::unique(Account::class),
            ],
            'name' => ['required', 'string', 'max:250'],
            'password' => $this->passwordRules(),
        ])->validate();

        return Account::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
