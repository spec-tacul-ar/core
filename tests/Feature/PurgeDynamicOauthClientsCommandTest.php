<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\AuthCode;
use Laravel\Passport\Client;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
use Tests\TestCase;

class PurgeDynamicOauthClientsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_only_unused_stale_dynamically_registered_clients(): void
    {
        $deleted = $this->dynamicClient();
        $fresh = $this->dynamicClient(['created_at' => now()->subHours(23)]);
        $personalAccess = Client::factory()->asPersonalAccessTokenClient()->create([
            'created_at' => now()->subHours(25),
        ]);
        $owned = Client::factory()->asPublic()->create([
            'owner_id' => Account::factory(),
            'owner_type' => 'account',
            'created_at' => now()->subHours(25),
        ]);

        $withLiveToken = $this->dynamicClient();
        $this->token($withLiveToken, expiresAt: now()->addHour());

        $withLiveRefreshToken = $this->dynamicClient();
        $refreshableToken = $this->token($withLiveRefreshToken, revoked: true, expiresAt: now()->subHour());
        RefreshToken::query()->create([
            'id' => str()->random(80),
            'access_token_id' => $refreshableToken->id,
            'revoked' => false,
            'expires_at' => now()->addHour(),
        ]);

        $withPendingAuthCode = $this->dynamicClient();
        AuthCode::query()->create([
            'id' => str()->random(80),
            'user_id' => Account::factory()->create()->id,
            'client_id' => $withPendingAuthCode->id,
            'revoked' => false,
            'expires_at' => now()->addHour(),
        ]);

        $withExpiredCredentials = $this->dynamicClient();
        $expiredToken = $this->token($withExpiredCredentials, revoked: true, expiresAt: now()->subHour());
        RefreshToken::query()->create([
            'id' => str()->random(80),
            'access_token_id' => $expiredToken->id,
            'revoked' => true,
            'expires_at' => now()->subHour(),
        ]);

        $this->artisan('spectacular:oauth-clean')->assertSuccessful();

        $this->assertModelMissing($deleted);
        $this->assertModelMissing($withExpiredCredentials);
        $this->assertModelExists($fresh);
        $this->assertModelExists($personalAccess);
        $this->assertModelExists($owned);
        $this->assertModelExists($withLiveToken);
        $this->assertModelExists($withLiveRefreshToken);
        $this->assertModelExists($withPendingAuthCode);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function dynamicClient(array $attributes = []): Client
    {
        return Client::factory()->asPublic()->create([
            'created_at' => now()->subHours(25),
            ...$attributes,
        ]);
    }

    private function token(Client $client, bool $revoked = false, ?\DateTimeInterface $expiresAt = null): Token
    {
        return Token::query()->create([
            'id' => str()->random(80),
            'client_id' => $client->id,
            'revoked' => $revoked,
            'expires_at' => $expiresAt,
        ]);
    }
}
