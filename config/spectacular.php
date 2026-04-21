<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mode
    |--------------------------------------------------------------------------
    |
    | This determines whether authentication and collaboration features are
    | enabled. The default .env file sets this to solo mode.
    |
    */

    'mode' => env('SPECTACULAR_MODE', 'team'),

    /*
    |--------------------------------------------------------------------------
    | Application path
    |--------------------------------------------------------------------------
    |
    | Changing this value will move the application away from the root URL.
    | This is useful if you want to have a custom landing page.
    |
    */

    'path' => env('SPECTACULAR_PATH', ''),

];
