<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OauthCommandTest extends TestCase
{
    use RefreshDatabase;

    protected string $passportKeyPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportKeyPath = sys_get_temp_dir() . '/spectacular-passport-' . str()->uuid();

        mkdir($this->passportKeyPath, 0777, true);

        Passport::loadKeysFrom($this->passportKeyPath);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->passportKeyPath . '/*') ?: [] as $file) {
            unlink($file);
        }

        rmdir($this->passportKeyPath);

        Passport::$keyPath = null;

        parent::tearDown();
    }

    public function test_oauth_command_generates_keys_and_creates_a_personal_access_client(): void
    {
        $this->artisan('spectacular:oauth')
            ->expectsOutput('OAuth encryption keys created.')
            ->expectsOutput('OAuth personal access client created.')
            ->assertSuccessful();

        $this->assertFileExists($this->passportKeyPath . '/oauth-public.key');
        $this->assertFileExists($this->passportKeyPath . '/oauth-private.key');
        $this->assertPersonalAccessClientCount(1);
    }

    public function test_oauth_command_does_not_regenerate_existing_keys_or_duplicate_client(): void
    {
        file_put_contents($this->passportKeyPath . '/oauth-public.key', 'existing-public-key');
        file_put_contents($this->passportKeyPath . '/oauth-private.key', 'existing-private-key');

        app(ClientRepository::class)->createPersonalAccessGrantClient(config('app.name'), config('auth.guards.api.provider'));

        $this->artisan('spectacular:oauth')
            ->expectsOutput('OAuth encryption keys not created. They might already exist.')
            ->expectsOutput('OAuth personal access client already exists.')
            ->assertSuccessful();

        $this->assertSame('existing-public-key', file_get_contents($this->passportKeyPath . '/oauth-public.key'));
        $this->assertSame('existing-private-key', file_get_contents($this->passportKeyPath . '/oauth-private.key'));
        $this->assertPersonalAccessClientCount(1);
    }

    public function test_oauth_command_ignores_personal_access_clients_for_other_providers(): void
    {
        app(ClientRepository::class)->createPersonalAccessGrantClient(config('app.name'), 'other-provider');

        $this->artisan('spectacular:oauth')
            ->expectsOutput('OAuth encryption keys created.')
            ->expectsOutput('OAuth personal access client created.')
            ->assertSuccessful();

        $this->assertPersonalAccessClientCount(2);
        $this->assertPersonalAccessClientCount(1, config('auth.guards.api.provider'));
    }

    protected function assertPersonalAccessClientCount(int $count, ?string $provider = null): void
    {
        $clients = Passport::client()
            ->newQuery()
            ->where('revoked', false)
            ->get()
            ->filter(fn($client): bool => $client->hasGrantType('personal_access'))
            ->when($provider, fn($clients) => $clients->where('provider', $provider));

        $this->assertCount($count, $clients);
    }
}
