<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Token;
use Tests\TestCase;

class TokenPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounts_can_only_delete_their_own_tokens(): void
    {
        $account = Account::factory()->create();
        $other = Account::factory()->create();

        $token = new Token([
            // Passport stores the account SQID in oauth_access_tokens.user_id.
            'user_id' => $account->sqid,
        ]);

        $this->assertTrue(Gate::forUser($account)->allows('delete', $token));
        $this->assertFalse(Gate::forUser($other)->allows('delete', $token));
    }
}
