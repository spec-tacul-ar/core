<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FortifyAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_account(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'New Account',
            'email' => 'new@example.test',
            'password' => 'password',
        ]);

        $response->assertCreated();

        $account = Account::query()->where('email', 'new@example.test')->firstOrFail();

        $this->assertSame('New Account', $account->name);
        $this->assertTrue(Hash::check('password', $account->password));
    }

    public function test_registration_rejects_duplicate_email_addresses(): void
    {
        Account::factory()->create(['email' => 'taken@example.test']);

        $this->postJson('/api/auth/register', [
            'name' => 'New Account',
            'email' => 'taken@example.test',
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_login_and_logout_use_the_web_session(): void
    {
        $account = Account::factory()->create([
            'email' => 'login@example.test',
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => $account->email,
            'password' => 'password',
        ])->assertOk();

        $this->getJson('/api/auth/account')
            ->assertOk()
            ->assertJsonPath('data.id', $account->id);

        $this->postJson('/api/auth/logout')->assertNoContent();

        $this->assertGuest('web');
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        Account::factory()->create([
            'email' => 'login@example.test',
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'login@example.test',
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_password_reset_request_sends_a_reset_notification(): void
    {
        Notification::fake();

        $account = Account::factory()->create(['email' => 'reset@example.test']);

        $this->postJson('/api/auth/password/request', [
            'email' => $account->email,
        ])->assertOk();

        Notification::assertSentTo($account, ResetPassword::class);
    }

    public function test_password_reset_changes_the_account_password(): void
    {
        $account = Account::factory()->create([
            'email' => 'reset@example.test',
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::broker('accounts')->createToken($account);

        $this->postJson('/api/auth/password/reset', [
            'email' => $account->email,
            'password' => 'new-password',
            'token' => $token,
        ])->assertOk();

        $this->assertTrue(Hash::check('new-password', $account->fresh()->password));
    }

    public function test_password_reset_rejects_invalid_tokens(): void
    {
        $account = Account::factory()->create(['email' => 'reset@example.test']);

        $this->postJson('/api/auth/password/reset', [
            'email' => $account->email,
            'password' => 'new-password',
            'token' => 'not-the-token',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_email_verification_marks_the_authenticated_account_as_verified(): void
    {
        $account = Account::factory()->unverified()->create();

        $this->actingAs($account);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(5),
            [
                'id' => $account->getKey(),
                'hash' => sha1($account->getEmailForVerification()),
            ],
        );

        $this->get($url)->assertRedirect('/?verified=1');

        $this->assertTrue($account->fresh()->hasVerifiedEmail());
    }

    public function test_password_confirmation_records_the_confirmation_in_session(): void
    {
        $account = Account::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($account);

        $this->postJson('/api/auth/password/confirm', [
            'password' => 'password',
        ])->assertCreated();

        $this->getJson('/api/auth/password/confirmed')
            ->assertOk()
            ->assertJsonPath('confirmed', true);
    }
}
