<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'itsenda' => [
        'app_key' => env('ITSENDA_APP_KEY', '70f208fb-0ebe-4f6c-9d01-84f572232c3d'),
        'auth_key' => env('ITSENDA_AUTH_KEY', 'g960JBdmz8VjbVGD3xpJshDdYPVlbnJVsdUVgZsbVXOaK6YgBZ'),
        'base_url' => env('ITSENDA_BASE_URL', 'https://itsenda.com/api'),
    ],

];
