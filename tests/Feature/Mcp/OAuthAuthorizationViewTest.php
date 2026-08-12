<?php

namespace Tests\Feature\Mcp;

use App\Models\Account;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Laravel\Passport\Scope;
use Tests\TestCase;

class OAuthAuthorizationViewTest extends TestCase
{
    public function test_authorization_view_shows_requested_scopes_and_redirect_uri(): void
    {
        $this->withoutVite();

        $client = new Client([
            'id' => 'test-client',
            'name' => 'Test MCP Client',
        ]);

        $request = Request::create('/oauth/authorize', 'GET', [
            'redirect_uri' => 'https://client.example/callback',
        ]);

        $this->view('mcp.authorize', [
            'authToken' => 'test-auth-token',
            'client' => $client,
            'request' => $request,
            'scopes' => [new Scope('mcp:use', 'Use MCP server')],
            'user' => Account::factory()->make(['email' => 'person@example.test']),
        ])
            ->assertSee('Permissions requested')
            ->assertSee('mcp:use')
            ->assertSee('Use MCP server')
            ->assertSee('Redirect URI')
            ->assertSee('https://client.example/callback');
    }
}
