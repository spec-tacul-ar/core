<?php

function auth_providers()
{
    return collect(config('services'))
        ->whereNotNull('client_id')
        ->keys()
        ->intersect(['github', 'google', 'linkedin'])
        ->values()
        ->toArray();
}
