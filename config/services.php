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

    'ai_review' => [
        'driver' => env('AI_REVIEW_DRIVER', 'gemini'),
    ],

    'repo_url' => env('REPO_URL', 'http://127.0.0.1:8001'),

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'review_key' => env('GEMINI_REVIEW_API_KEY', env('GEMINI_API_KEY')),
        'plagiarism_key' => env('GEMINI_PLAGIARISM_API_KEY', env('GEMINI_API_KEY')),
        'chatbot_key' => env('GEMINI_CHATBOT_API_KEY', env('GEMINI_API_KEY')),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
    ],

    'plagiarism_paraphrase' => [
        'driver' => env('PLAGIARISM_PARAPHRASE_DRIVER', 'gemini'),
    ],

];
