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

    /*
    |--------------------------------------------------------------------------
    | SQIDs
    |--------------------------------------------------------------------------
    |
    | Resource IDs are converted to short alphanumeric strings like YouTube
    | IDs for readability. If you're hosting Spectacular publically, you should
    | provide your own shuffled alphabet in the .env file so IDs are less
    | predictable.
    |
    */

    'sqids' => [
        'alphabet' => env('SPECTACULAR_SQIDS_ALPHABET', 'abcdefghijklmnopqrstuvwxyz0123456789'),
        'length' => env('SPECTACULAR_SQIDS_LENGTH', 6),
    ],

];
