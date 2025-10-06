# Laravel GoSms Package Installation

## Step 1: Install the package

```bash
composer require ecomailcz/laravel-gosms
```

## Step 2: Publish configuration

```bash
php artisan vendor:publish --provider="EcomailGoSms\Laravel\GoSmsServiceProvider" --tag="config"
```

## Step 3: Set environment variables

Add to your `.env` file:

```env
GOSMS_CLIENT_ID=your_client_id
GOSMS_CLIENT_SECRET=your_client_secret
GOSMS_DEFAULT_CHANNEL=1
```

## Step 4: Register Service Provider (optional)

If the package doesn't register automatically, add to `config/app.php`:

```php
'providers' => [
    // ...
    EcomailGoSms\Laravel\GoSmsServiceProvider::class,
],

'aliases' => [
    // ...
    'GoSms' => EcomailGoSms\Laravel\GoSmsFacade::class,
],
```

## Step 5: Testing

Create a test controller or use tinker:

```bash
php artisan tinker
```

```php
use EcomailGoSms\Laravel\GoSmsFacade as GoSms;

// Test SMS sending
$result = GoSms::authenticate()->sendSms('+420123456789', 'Test message');
```

## Package structure

```
src/
├── Laravel/
│   ├── GoSmsServiceProvider.php    # Laravel Service Provider
│   └── GoSmsFacade.php            # Laravel Facade
├── GoSmsClient.php                # Main API client
├── AuthorizationException.php     # Exceptions
├── InvalidFormat.php
├── InvalidNumber.php
└── RequestException.php

config/
└── gosms.php                     # Configuration file

tests/
├── TestCase.php                  # Base test class
└── GoSmsClientTest.php          # Tests

examples/
└── laravel-usage.php            # Usage examples
```

## Supported Laravel versions

- Laravel 9.x
- Laravel 10.x  
- Laravel 11.x

## Requirements

- PHP 8.1+
- Laravel 9.0+
- Guzzle HTTP Client
- libphonenumber-for-php-lite
