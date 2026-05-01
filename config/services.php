<?php

return [
    'payment' => [
        'url' => env('PAYMENT_GATEWAY_URL'),
        'key' => env('PAYMENT_GATEWAY_KEY'),
    ],

    'telegram' => [
        'token'   => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],
];
