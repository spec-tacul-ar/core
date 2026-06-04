<?php

namespace App\Policies;

use App\Models\Account;
use Laravel\Passport\Token;

class TokenPolicy
{
    public function view(Account $account, Token $token): bool
    {
        // Passport stores the account SQID in oauth_access_tokens.user_id.
        return $token->user_id === $account->sqid;
    }

    public function create(Account $account): bool
    {
        return true;
    }

    public function update(Account $account, Token $token): bool
    {
        return false;
    }

    public function delete(Account $account, Token $token): bool
    {
        // Passport stores the account SQID in oauth_access_tokens.user_id.
        return $token->user_id === $account->sqid;
    }
}
