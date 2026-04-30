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

    /*
    |--------------------------------------------------------------------------
    | SPID/CIE — OIDC proxy (external service, e.g. IAM Proxy Italia)
    |--------------------------------------------------------------------------
    | base_url: issuer root. Endpoints are derived as {base_url}/authorization,
    |           /token, /userinfo unless overridden below.
    | Override individual endpoints via OIDC_*_ENDPOINT env vars when the proxy
    | exposes them at non-standard paths.
    */
    /*
    |--------------------------------------------------------------------------
    | OSRM — Self-hosted routing engine
    |--------------------------------------------------------------------------
    */
    'osrm' => [
        'base_url' => env('OSRM_BASE_URL', 'http://osrm:5000'),
        'timeout' => (int) env('OSRM_TIMEOUT', 10),
    ],

    'oidc' => [
        'client_id' => env('OIDC_CLIENT_ID'),
        'client_secret' => env('OIDC_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/auth/callback',
        'base_url' => env('OIDC_URL'),
        'authorization_endpoint' => env('OIDC_AUTHORIZATION_ENDPOINT'),
        'token_endpoint' => env('OIDC_TOKEN_ENDPOINT'),
        'userinfo_endpoint' => env('OIDC_USERINFO_ENDPOINT'),
    ],

];
