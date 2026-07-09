<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class TokenEndpointsTest extends TestCase
{
    use BuildsApiFixtures;
    use RefreshDatabase;

    protected ?string $passportKeyPath = null;

    protected ?Client $personalAccessClient = null;

    protected function tearDown(): void
    {
        if ($this->passportKeyPath) {
            foreach (glob($this->passportKeyPath . '/*') ?: [] as $file) {
                unlink($file);
            }

            rmdir($this->passportKeyPath);
        }

        Passport::$keyPath = null;

        parent::tearDown();
    }

    public function test_tokens_index_returns_only_active_tokens_for_the_authenticated_account(): void
    {
        $account = Account::factory()->create();
        $other = Account::factory()->create();

        $active = $this->createTokenRecord($account, ['name' => 'Active token']);
        $this->createTokenRecord($account, ['expires_at' => now()->subMinute(), 'name' => 'Expired token']);
        $this->createTokenRecord($account, ['name' => 'Revoked token', 'revoked' => true]);
        $this->createTokenRecord($other, ['name' => 'Other token']);

        $this->actingAsAccount($account);

        $this->getJson('/api/tokens')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.name', 'Active token')
            ->assertJsonPath('data.0.revoked', false);
    }

    public function test_tokens_revoke_endpoint_allows_accounts_to_revoke_only_their_own_tokens(): void
    {
        $account = Account::factory()->create();
        $other = Account::factory()->create();

        $ownToken = $this->createTokenRecord($account);
        $ownRefreshToken = $this->createRefreshTokenRecord($ownToken);
        $otherToken = $this->createTokenRecord($other);

        $this->actingAsAccount($account);

        $this->postJson('/api/tokens/' . $otherToken->id . '/revoke')
            ->assertForbidden();

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $otherToken->id,
            'revoked' => false,
        ]);

        $this->postJson('/api/tokens/' . $ownToken->id . '/revoke')
            ->assertNoContent();

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $ownToken->id,
            'revoked' => true,
        ]);

        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'id' => $ownRefreshToken->id,
            'revoked' => true,
        ]);
    }

    public function test_tokens_create_endpoint_issues_a_personal_access_token_for_the_authenticated_account(): void
    {
        $this->preparePassportKeys();
        $this->artisan('passport:keys')->assertSuccessful();
        $this->personalAccessClient();

        $account = Account::factory()->create();

        $this->actingAsAccount($account);

        $this->postJson('/api/tokens', [
            'name' => 'Local automation',
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Local automation')
            ->assertJsonPath('data.revoked', false)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'secret',
                    'revoked',
                    'created_at',
                    'updated_at',
                    'expires_at',
                ],
            ]);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'name' => 'Local automation',
            'revoked' => false,
            'user_id' => $account->sqid,
        ]);

        $this->assertSame(['mcp:use'], Token::query()->where('name', 'Local automation')->firstOrFail()->scopes);
    }

    public function test_mcp_bearer_tokens_cannot_create_tokens(): void
    {
        $account = Account::factory()->create();

        $this->actingAsAccount($account, ['mcp:use']);

        $this->postJson('/api/tokens', [
            'name' => 'Disallowed token',
        ])->assertForbidden();

        $this->assertDatabaseMissing('oauth_access_tokens', [
            'name' => 'Disallowed token',
        ]);
    }

    public function test_bearer_tokens_cannot_call_general_api_endpoints(): void
    {
        $account = Account::factory()->create();

        $this->actingAsAccount($account, ['mcp:use']);

        $this->getJson('/api/account')->assertForbidden();
    }

    private function createTokenRecord(Account $account, array $attributes = []): Token
    {
        return Token::query()->forceCreate([
            'id' => $attributes['id'] ?? str()->random(40),
            'user_id' => $account->sqid,
            'client_id' => ($attributes['client'] ?? $this->personalAccessClient())->id,
            'name' => $attributes['name'] ?? 'Test token',
            'scopes' => $attributes['scopes'] ?? [],
            'revoked' => $attributes['revoked'] ?? false,
            'expires_at' => $attributes['expires_at'] ?? now()->addDay(),
        ]);
    }

    private function createRefreshTokenRecord(Token $token, array $attributes = []): RefreshToken
    {
        return RefreshToken::query()->forceCreate([
            'id' => $attributes['id'] ?? str()->random(40),
            'access_token_id' => $token->id,
            'revoked' => $attributes['revoked'] ?? false,
            'expires_at' => $attributes['expires_at'] ?? now()->addDay(),
        ]);
    }

    private function personalAccessClient(): Client
    {
        return $this->personalAccessClient ??= app(ClientRepository::class)
            ->createPersonalAccessGrantClient(config('app.name'), config('auth.guards.api.provider'));
    }

    private function preparePassportKeys(): void
    {
        $this->passportKeyPath = sys_get_temp_dir() . '/spectacular-passport-' . str()->uuid();

        mkdir($this->passportKeyPath, 0777, true);

        Passport::loadKeysFrom($this->passportKeyPath);
    }
}
