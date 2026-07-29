<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;
use Tests\TestCase;

class FortifyAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_account(): void
    {
        $response = $this->postJson('/auth/register', [
            'name' => 'New Account',
            'email' => 'new@example.test',
            'password' => 'password',
        ]);

        $response->assertCreated();

        $account = Account::query()->where('email', 'new@example.test')->firstOrFail();

        $this->assertSame('New Account', $account->name);
        $this->assertTrue(Hash::check('password', $account->password));
    }

    public function test_registration_sends_an_email_verification_notification(): void
    {
        Notification::fake();

        $this->postJson('/auth/register', [
            'name' => 'New Account',
            'email' => 'new@example.test',
            'password' => 'password',
        ])->assertCreated();

        $account = Account::query()->where('email', 'new@example.test')->firstOrFail();

        Notification::assertSentTo($account, VerifyEmail::class);
    }

    public function test_registration_rejects_duplicate_email_addresses(): void
    {
        Account::factory()->create(['email' => 'taken@example.test']);

        $this->postJson('/auth/register', [
            'name' => 'New Account',
            'email' => 'taken@example.test',
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_registration_rejects_email_addresses_without_a_domain_suffix(): void
    {
        $this->postJson('/auth/register', [
            'name' => 'New Account',
            'email' => 'new@spectacular',
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_registration_and_verification_follow_spectacular_enable_flags(): void
    {
        $previousRegistration = $this->environmentVariable('SPECTACULAR_ENABLE_REGISTRATION');
        $previousVerification = $this->environmentVariable('SPECTACULAR_ENABLE_VERIFICATION');

        try {
            $this->setEnvironmentVariable('SPECTACULAR_ENABLE_REGISTRATION', 'false');
            $this->setEnvironmentVariable('SPECTACULAR_ENABLE_VERIFICATION', 'false');

            $spectacular = require config_path('spectacular.php');
            $fortify = require config_path('fortify.php');
            $features = array_filter($fortify['features']);

            $this->assertFalse($spectacular['registration']);
            $this->assertFalse($spectacular['verification']);
            $this->assertNotContains(Features::registration(), $features);
            $this->assertContains(Features::resetPasswords(), $features);
            $this->assertNotContains(Features::emailVerification(), $features);
        } finally {
            $this->restoreEnvironmentVariable('SPECTACULAR_ENABLE_REGISTRATION', $previousRegistration);
            $this->restoreEnvironmentVariable('SPECTACULAR_ENABLE_VERIFICATION', $previousVerification);
        }
    }

    private function environmentVariable(string $name): array
    {
        return [
            'environment' => $_ENV[$name] ?? null,
            'server' => $_SERVER[$name] ?? null,
            'process' => getenv($name),
        ];
    }

    private function setEnvironmentVariable(string $name, string $value): void
    {
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }

    private function restoreEnvironmentVariable(string $name, array $value): void
    {
        $value['process'] === false
            ? putenv($name)
            : putenv("{$name}={$value['process']}");

        if ($value['environment'] === null) {
            unset($_ENV[$name]);
        } else {
            $_ENV[$name] = $value['environment'];
        }

        if ($value['server'] === null) {
            unset($_SERVER[$name]);
        } else {
            $_SERVER[$name] = $value['server'];
        }
    }

    public function test_login_and_logout_use_the_web_session(): void
    {
        $account = Account::factory()->create([
            'email' => 'login@example.test',
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/auth/login', [
            'email' => $account->email,
            'password' => 'password',
        ])->assertOk();

        $this->getJson('/auth/check')
            ->assertOk()
            ->assertJsonPath('is_authenticated', true)
            ->assertJsonPath('is_verified', true);

        $this->postJson('/auth/logout')->assertNoContent();

        $this->assertGuest('web');
    }

    public function test_database_sessions_store_the_account_sqid(): void
    {
        $previousDriver = config('session.driver');

        try {
            config(['session.driver' => 'database']);
            app('session')->forgetDrivers();

            $account = Account::factory()->create([
                'email' => 'database-session@example.test',
                'password' => Hash::make('password'),
            ]);

            $this->postJson('/auth/login', [
                'email' => $account->email,
                'password' => 'password',
            ])->assertOk();

            $this->assertDatabaseHas('sessions', [
                'user_id' => $account->sqid,
            ]);
        } finally {
            config(['session.driver' => $previousDriver]);
            app('session')->forgetDrivers();
        }
    }

    public function test_remember_tokens_retrieve_accounts_by_sqid(): void
    {
        $account = Account::factory()->create([
            'remember_token' => 'remember-token',
        ]);

        $retrievedAccount = Auth::createUserProvider('accounts')
            ->retrieveByToken($account->sqid, 'remember-token');

        $this->assertTrue($account->is($retrievedAccount));
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        Account::factory()->create([
            'email' => 'login@example.test',
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/auth/login', [
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

        $this
            ->withServerVariables(['HTTP_HOST' => 'attacker.example'])
            ->postJson('/auth/password/request', [
                'email' => $account->email,
            ])
            ->assertOk();

        Notification::assertSentTo($account, ResetPassword::class, function (ResetPassword $notification) use ($account) {
            $actionUrl = $notification->toMail($account)->actionUrl;
            $actionUrlOrigin = parse_url($actionUrl, PHP_URL_SCHEME) . '://' . parse_url($actionUrl, PHP_URL_HOST);

            if ($port = parse_url($actionUrl, PHP_URL_PORT)) {
                $actionUrlOrigin .= ':' . $port;
            }

            $this->assertSame(config('app.url'), $actionUrlOrigin);
            $this->assertStringStartsWith('/app/password/reset/', parse_url($actionUrl, PHP_URL_PATH));

            return true;
        });
    }

    public function test_password_reset_changes_the_account_password(): void
    {
        $account = Account::factory()->create([
            'email' => 'reset@example.test',
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::broker('accounts')->createToken($account);

        $this->postJson('/auth/password/reset', [
            'email' => $account->email,
            'password' => 'new-password',
            'token' => $token,
        ])->assertOk();

        $this->assertTrue(Hash::check('new-password', $account->fresh()->password));
    }

    public function test_password_reset_verifies_the_account_email_address(): void
    {
        $account = Account::factory()->unverified()->create([
            'email' => 'reset@example.test',
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::broker('accounts')->createToken($account);

        $this->postJson('/auth/password/reset', [
            'email' => $account->email,
            'password' => 'new-password',
            'token' => $token,
        ])->assertOk();

        $account->refresh();

        $this->assertTrue(Hash::check('new-password', $account->password));
        $this->assertTrue($account->hasVerifiedEmail());
    }

    public function test_password_reset_rejects_invalid_tokens(): void
    {
        $account = Account::factory()->create(['email' => 'reset@example.test']);

        $this->postJson('/auth/password/reset', [
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

        $this->get($this->emailVerificationUrl($account))
            ->assertRedirect('/app?verified=1');

        $this->assertTrue($account->fresh()->hasVerifiedEmail());
    }

    public function test_email_verification_redirects_to_settings_page_under_the_application_path(): void
    {
        config()->set('fortify.redirects.email-verification', '/app/account/settings');

        $account = Account::factory()->unverified()->create();

        $this->actingAs($account);

        $this->get($this->emailVerificationUrl($account))
            ->assertRedirect('/app/account/settings?verified=1');

        $this->assertTrue($account->fresh()->hasVerifiedEmail());
    }

    public function test_email_verification_notification_can_be_resent(): void
    {
        Notification::fake();

        $account = Account::factory()->unverified()->create();
        $this->actingAs($account);

        $this->postJson('/email/verification-notification')->assertAccepted();

        Notification::assertSentTo($account, VerifyEmail::class);
    }

    public function test_password_confirmation_records_the_confirmation_in_session(): void
    {
        $account = Account::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($account);

        $this->postJson('/auth/password/confirm', [
            'password' => 'password',
        ])->assertCreated();

        $this->getJson('/auth/password/confirmed')
            ->assertOk()
            ->assertJsonPath('confirmed', true);
    }

    private function emailVerificationUrl(Account $account): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(5),
            [
                'id' => $account->getKey(),
                'hash' => sha1($account->getEmailForVerification()),
            ],
        );
    }
}
