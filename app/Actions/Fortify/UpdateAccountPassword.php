<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use App\Models\Account;

class UpdateAccountPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the account's password.
     *
     * @param  array<string, string>  $input
     */
    public function update(Account $account, array $input): void
    {
        Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ], [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');

        $account->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
