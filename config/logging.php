<?php

return [
    'channels' => [
        'glitter_social_login_totp' => [
            'driver' => 'daily',
            'path' => storage_path('logs/glitter_social_login_totp.log'),
            'level' => env('GLITTER_SOCIAL_LOGIN_TOTP_LOG_LEVEL', 'warning'),
            'days' => 14,
            'replace_placeholders' => true,
        ],
    ],
];
