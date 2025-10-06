<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Načtení .env souboru
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$client = new EcomailGoSms\Client();

$response = $client->makeRequest('POST', 'auth/token', [
    'username' => $_ENV['GOSMS_PUBLIC'],
    'password' => $_ENV['GOSMS_PRIVATE'],
    'grant_type' => 'password',
]);

dd($response);
