<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Service Configuration
    |--------------------------------------------------------------------------
    |
    | إعداد خدمة الذكاء الاصطناعي لتحليل وفلترة السير الذاتية.
    | يدعم: 'openai', 'deepseek'
    |
    */

    'provider' => env('AI_SERVICE_PROVIDER', 'openai'),

    'openai' => [
        'api_key' => env('AI_SERVICE_API_KEY'),
        'base_url' => env('AI_SERVICE_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('AI_SERVICE_MODEL', 'gpt-4o-mini'),
    ],

    'deepseek' => [
        'api_key' => env('AI_SERVICE_API_KEY'),
        'base_url' => env('AI_SERVICE_BASE_URL', 'https://api.deepseek.com/v1'),
        'model' => env('AI_SERVICE_MODEL', 'deepseek-chat'),
    ],

    'gemini' => [
        'api_key' => env('AI_SERVICE_API_KEY'),
        'base_url' => env('AI_SERVICE_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'model' => env('AI_SERVICE_MODEL', 'gemini-2.5-flash'),
    ],

    'timeout' => env('AI_SERVICE_TIMEOUT', 30),
];
