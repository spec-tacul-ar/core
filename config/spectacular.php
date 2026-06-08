<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    |
    | Use this to disable registration. Accounts can still be created using an
    | Artisan command.
    |
    */

    'registration' => env('SPECTACULAR_ENABLE_REGISTRATION', true),

    /*
    |--------------------------------------------------------------------------
    | Enable Verification
    |--------------------------------------------------------------------------
    |
    | Use this to disable email verification.
    |
    */

    'verification' => env('SPECTACULAR_ENABLE_VERIFICATION', true),

    /*
    |--------------------------------------------------------------------------
    | Socialite Providers
    |--------------------------------------------------------------------------
    |
    | List the OAuth providers that should be available on the frontend.
    |
    */

    'socialite' => [
        'google' => !empty(env('GOOGLE_CLIENT_ID')),
        'github' => !empty(env('GITHUB_CLIENT_ID')),
        'linkedin-openid' => !empty(env('LINKEDIN_CLIENT_ID')),
    ],

    /*
    |--------------------------------------------------------------------------
    | SQIDs
    |--------------------------------------------------------------------------
    |
    | Resource IDs are converted to short alphanumeric strings like YouTube
    | IDs for readability. If you're hosting Spectacular publicly, you should
    | provide your own shuffled alphabet in the .env file so IDs are less
    | predictable.
    |
    */

    'sqids' => [
        'alphabet' => env('SPECTACULAR_SQIDS_ALPHABET', 'abcdefghijklmnopqrstuvwxyz0123456789'),
        'length' => env('SPECTACULAR_SQIDS_LENGTH', 6),
    ],

];
