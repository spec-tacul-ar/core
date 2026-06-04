<?php

use App\Mcp\Servers\SpecificationsServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('mcp/specifications', SpecificationsServer::class)
    ->middleware(['auth:api', 'verified', 'throttle:api']);
