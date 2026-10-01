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
    'stripe' => [
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'aussiepacific' => [
        'base_url' => env('AUSSIEPACIFIC_API_URL', 'https://api.aussiepacific.co.nz'),
        'token'    => env('AUSSIEPACIFIC_API_KEY'),
    ],

    'banbury' => [
        'base_url' => env('BANBURY_API_URL', 'https://apical.beyondexpectations.co.nz'),
        'username' => env('BANBURY_API_USERNAME'),
        'password' => env('BANBURY_API_PASSWORD'),
    ],

    'logoline' => [
        'base_url'      => env('LOGOLINE_API_URL', 'https://api.logoline.com.au'),
        'client_id'     => env('LOGOLINE_CLIENT_ID'),
        'client_secret' => env('LOGOLINE_CLIENT_SECRET'),
    ],

    'fashionbiz' => [
        'base_url' => env('FASHIONBIZ_API_URL', 'https://www.fashionbizapis.com'),
        'token'    => env('FASHIONBIZ_API_TOKEN'),
        'brand'    => env('FASHIONBIZ_BRAND', 'biz-collection'),
        'branch'   => env('FASHIONBIZ_BRANCH', 'nz'),
    ],

    'trends' => [
        'base_url' => env('TRENDS_API_URL', 'https://nz.api.trends.nz'),
        'token'    => env('TRENDS_API_TOKEN'),
    ],

    'premiumapparel' => [
        'base_url' => env('PREMIUMAPPAREL_API_URL', 'https://hq.premiumapparel.co.nz/api'),
        'token'    => env('PREMIUMAPPAREL_API_TOKEN'),
    ],

];
