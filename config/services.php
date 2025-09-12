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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'upstox' => [
        'api_key' => env('UPSTOX_API_KEY'),
        'base_url' => env('UPSTOX_BASE_URL', 'https://api.upstox.com/v2'),
    ],

    'truedata' => [
        'username' => env('TRUEDATA_USERNAME', 'Trial110'),
        'password' => env('TRUEDATA_PASSWORD', 'bikash110'),
        'realtime_port' => env('TRUEDATA_PORT', 8086), // Sandbox port for trial
        'base_url' => env('TRUEDATA_HOST', 'push.truedata.in'), // Correct host
    ],

    'alphavantage' => [
        'api_key' => env('ALPHAVANTAGE_API_KEY', 'II73DAUC1KB906VM'),
        'base_url' => env('ALPHAVANTAGE_BASE_URL', 'https://www.alphavantage.co/query'),
        'cache_ttl' => env('ALPHAVANTAGE_CACHE_TTL', 300), // 5 minutes cache
    ],

    'office365' => [
        'client_id' => env('O365_CLIENT_ID'),
        'client_secret' => env('O365_CLIENT_SECRET'),
        'redirect' => env('O365_REDIRECT_URI'),
    ],

];
