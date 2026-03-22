<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use App\Models\User;

class ResetAccountPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the account's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $account, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $account->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
