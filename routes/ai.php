<?php

use App\Mcp\Servers\SpecificationsServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;
use Laravel\Passport\Http\Middleware\CheckToken;

Route::middleware('throttle:mcp-oauth-registration')->group(function (): void {
    Mcp::oauthRoutes();
});

Mcp::web('mcp/specifications', SpecificationsServer::class)
    ->middleware(['auth:api', CheckToken::using('mcp:use'), 'verified']);
