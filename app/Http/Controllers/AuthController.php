<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use App\Models\Account;

class AuthController extends Controller
{
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        try {
            $social = Socialite::driver($provider)->user();
        } catch (InvalidStateException $exception) {
            report($exception);

            return response()->view('auth.error', ['message' => 'Something went while we were communicating with the OAuth provider.'], 400);
        }

        $account = Account::findBySocial($provider, $social->id);

        if (!$account) {
            try {
                $validated = Validator::make([
                    'name' => $social->getName(),
                    'email' => $social->getEmail(),
                ], [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255', Rule::unique(Account::class)],
                ])->validate();
            } catch (ValidationException $exception) {
                return response()->view('auth.error', ['message' => 'Could not create your account. ' . $exception->getMessage()], 422);
            }

            $account = new Account($validated);
            $account->socialite_provider = $provider;
            $account->socialite_provider_id = $social->id;
            $account->save();
        }

        Auth::login($account, true);

        return redirect('/app');
    }
}
