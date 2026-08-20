<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use Mockery;
use Tests\TestCase;

class SocialiteAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_socialite_routes_reject_unknown_providers_before_resolving_a_driver(): void
    {
        Socialite::shouldReceive('driver')->never();

        $this->get('/auth/not-configured/redirect')->assertNotFound();
        $this->get('/auth/not-configured/callback')->assertNotFound();
    }

    public function test_socialite_signup_follows_registration_flag(): void
    {
        config(['spectacular.registration' => false]);
        $this->mockSocialiteUser('github', 'social-123', 'New Account', 'new@example.test');

        $this->get('/auth/github/callback')
            ->assertNotFound();

        $this->assertGuest('web');
        $this->assertDatabaseMissing('accounts', [
            'email' => 'new@example.test',
        ]);
    }

    public function test_socialite_login_allows_existing_social_accounts_when_registration_is_disabled(): void
    {
        config(['spectacular.registration' => false]);

        $account = Account::factory()->create([
            'socialite_provider' => 'github',
            'socialite_provider_id' => 'social-123',
        ]);

        $this->mockSocialiteUser('github', 'social-123', 'Existing Account', $account->email);

        $this->get('/auth/github/callback')
            ->assertRedirect('/app');

        $this->assertAuthenticatedAs($account, 'web');
    }

    private function mockSocialiteUser(string $provider, string $id, string $name, string $email): void
    {
        $socialUser = (new User())->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ]);

        $socialiteProvider = Mockery::mock(Provider::class);
        $socialiteProvider->shouldReceive('user')
            ->once()
            ->andReturn($socialUser);

        Socialite::shouldReceive('driver')
            ->once()
            ->with($provider)
            ->andReturn($socialiteProvider);
    }
}
