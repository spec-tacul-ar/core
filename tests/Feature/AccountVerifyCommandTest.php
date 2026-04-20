<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountVerifyCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_verifies_an_unverified_account_by_email(): void
    {
        $account = Account::factory()->unverified()->create([
            'email' => 'invitee@example.test',
        ]);

        $this->artisan('spectacular:account:verify', [
            'email' => $account->email,
        ])
            ->expectsOutput('Account verified.')
            ->assertSuccessful();

        $this->assertTrue($account->fresh()->hasVerifiedEmail());
    }

    public function test_it_reports_when_an_account_does_not_exist(): void
    {
        $this->artisan('spectacular:account:verify', [
            'email' => 'missing@example.test',
        ])
            ->expectsOutput('No account found for that email address.')
            ->assertExitCode(2);
    }

    public function test_it_reports_when_an_account_is_already_verified(): void
    {
        $account = Account::factory()->create([
            'email' => 'verified@example.test',
        ]);

        $this->artisan('spectacular:account:verify', [
            'email' => $account->email,
        ])
            ->expectsOutput('Account is already verified.')
            ->assertSuccessful();

        $this->assertTrue($account->fresh()->hasVerifiedEmail());
    }
}
