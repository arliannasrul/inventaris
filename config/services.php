<?php

return [
    'prisma' => [
        'url' => env('PRISMA_API_URL', 'http://localhost:3000'),
        'token' => env('PRISMA_API_TOKEN', 'change-me'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],
];
