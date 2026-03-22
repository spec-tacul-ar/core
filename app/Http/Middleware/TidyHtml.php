<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TidyHtml
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (extension_loaded('tidy')) {
            $content = $response->getContent();

            if (! is_string($content) || $content === '') {
                return $response;
            }

            $options = [
                'indent' => true,
                'indent-spaces' => 4,
                'wrap' => 0,
                'drop-empty-elements' => false,
                'show-body-only' => false,
            ];

            $tidied = tidy_repair_string($content, $options, 'utf8');

            $response->setContent($tidied);
        }

        return $response;
    }
}
