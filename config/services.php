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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'firebase' => [
        'credentials_path' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase-credentials.json')),
        'database_url' => env('FIREBASE_DATABASE_URL'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
    ],

    'fcm' => [
        'enabled' => env('FCM_ENABLED', true),
        'batch_size' => env('FCM_BATCH_SIZE', 500),
        'retry_attempts' => env('FCM_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('FCM_RETRY_DELAY', 10), // seconds
        'default_ttl' => env('FCM_DEFAULT_TTL', 86400), // 24 hours
        'priority' => env('FCM_PRIORITY', 'high'),
        'stale_token_days' => env('FCM_STALE_TOKEN_DAYS', 30),
    ],

];
