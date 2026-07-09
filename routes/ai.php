<?php

use App\Mcp\Servers\SpecificationsServer;
use Laravel\Mcp\Facades\Mcp;
use Laravel\Passport\Http\Middleware\CheckToken;

Mcp::oauthRoutes();

Mcp::web('mcp/specifications', SpecificationsServer::class)
    ->middleware(['auth:api', CheckToken::using('mcp:use'), 'verified']);
