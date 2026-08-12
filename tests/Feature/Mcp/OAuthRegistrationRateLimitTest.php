<?php

namespace Tests\Feature\Mcp;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OAuthRegistrationRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_the_expected_public_ownerless_client(): void
    {
        $response = $this->postJson('/oauth/register', [
            'client_name' => 'MCP Client',
            'redirect_uris' => ['https://example.com/callback'],
        ])->assertCreated();

        $client = Passport::client()->newQuery()->findOrFail($response->json('client_id'));

        $this->assertNull($client->owner_id);
        $this->assertNull($client->owner_type);
        $this->assertNull($client->secret);
        $this->assertContains('authorization_code', $client->grant_types);
        $this->assertContains('refresh_token', $client->grant_types);
        $this->assertFalse($client->revoked);
    }

    public function test_registration_is_limited_by_ip_address(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/oauth/register')->assertBadRequest();
        }

        $this->postJson('/oauth/register')->assertTooManyRequests();
    }

    public function test_discovery_requests_do_not_consume_registration_attempts(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1']);

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->getJson('/.well-known/oauth-authorization-server')->assertOk();
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/oauth/register')->assertBadRequest();
        }

        $this->postJson('/oauth/register')->assertTooManyRequests();
    }

    public function test_registration_limiter_has_minute_and_hourly_limits(): void
    {
        $limiter = RateLimiter::limiter('mcp-oauth-registration');

        $this->assertNotNull($limiter);

        $limits = $limiter(Request::create('/oauth/register', 'POST', server: [
            'REMOTE_ADDR' => '203.0.113.1',
        ]));

        $this->assertSame([
            ['key' => '203.0.113.1:attempts:5:decay:60', 'maxAttempts' => 5, 'decaySeconds' => 60],
            ['key' => '203.0.113.1:attempts:25:decay:3600', 'maxAttempts' => 25, 'decaySeconds' => 3600],
        ], collect($limits)->map(fn($limit) => [
            'key' => $limit->key,
            'maxAttempts' => $limit->maxAttempts,
            'decaySeconds' => $limit->decaySeconds,
        ])->all());
    }
}
