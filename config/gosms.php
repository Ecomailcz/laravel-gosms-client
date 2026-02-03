<?php

declare(strict_types = 1);

return [
    'client_id' => env('GOSMS_CLIENT_ID', ''),
    'client_secret' => env('GOSMS_CLIENT_SECRET', ''),
    'default_channel' => (int) env('GOSMS_DEFAULT_CHANNEL', 1),
    'base_uri' => env('GOSMS_BASE_URI', 'https://api.gosms.eu/api/v2/'),
    'timeout' => (int) env('GOSMS_TIMEOUT', 30),
];
