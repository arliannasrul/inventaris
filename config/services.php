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

    'doku' => [
        'client_id'     => env('DOKU_CLIENT_ID'),
        'secret_key'    => env('DOKU_SECRET_KEY'),
        'is_production' => env('DOKU_IS_PRODUCTION', false),
        'base_url'      => env('DOKU_IS_PRODUCTION', false)
                            ? 'https://api.doku.com'
                            : 'https://sandbox.doku.com',
    ],
];
