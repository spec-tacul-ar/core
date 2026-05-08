<?php

namespace Tests\Feature;

use Tests\TestCase;

class MakeCachableTest extends TestCase
{
    public function test_folio_pages_do_not_set_session_or_xsrf_cookies(): void
    {
        $response = $this->get('/docs');

        $response->assertOk();
        $response->assertCookieMissing(config('session.cookie'));
        $response->assertCookieMissing('XSRF-TOKEN');
    }

    public function test_non_folio_web_routes_still_set_session_and_xsrf_cookies(): void
    {
        $response = $this->get('/app');

        $response->assertOk();
        $response->assertCookie(config('session.cookie'));
        $response->assertCookie('XSRF-TOKEN');
    }
}
