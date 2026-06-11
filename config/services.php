<?php

return [
    'prisma' => [
        'url'   => env('PRISMA_API_URL', 'http://localhost:3000'),
        'token' => env('PRISMA_API_TOKEN', 'change-me'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    'kiriminaja' => [
        'api_key'  => env('KIRIMINAJA_API_KEY'),
        'base_url' => env('KIRIMINAJA_BASE_URL', 'https://tdev.kiriminaja.com/api/wd/v1'),
    ],
];
