<?php

namespace Tests\Feature\Mcp;

use Laravel\Passport\Passport;
use Tests\TestCase;

class OAuthScopeIsolationTest extends TestCase
{
    public function test_mcp_is_the_only_scope_available_to_unrestricted_oauth_clients(): void
    {
        // Unrestricted Passport clients may request any registered scope, including "*".
        // Before adding scopes, ensure existing wildcard tokens cannot inherit them and
        // introduce client-level restrictions where necessary.
        $this->assertSame(['mcp:use'], Passport::scopeIds());
    }
}
