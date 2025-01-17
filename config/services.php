<?php

return [
    'postmark' => [
        'endpoint' => env('POSTMARK_ENDPOINT', 'https://api.postmarkapp.com'),
        'account' => env('POSTMARK_ACCOUNT_TOKEN'),
        'from_email' => env('POSTMARK_FROM_EMAIL'),
        'reply_to_email' => env('POSTMARK_REPLY_TO_EMAIL'),
        'return_path_domain' => env('POSTMARK_RETURN_PATH_DOMAIN'),
        'server' => env('POSTMARK_SERVER_TOKEN'),
    ],
];
