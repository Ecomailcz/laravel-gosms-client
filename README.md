# Laravel GoSms Package

Laravel package for GoSms.cz API client. This package provides an easy interface for sending SMS messages through GoSms.cz API in Laravel applications.

## Installation

### Composer

```bash
composer require ecomailcz/laravel-gosms
```

### Publishing Configuration

```bash
php artisan vendor:publish --provider="EcomailGoSms\Laravel\GoSmsServiceProvider" --tag="config"
```

## Configuration

After publishing the configuration file, set the following variables in `.env`:

```env
GOSMS_CLIENT_ID=your_client_id
GOSMS_CLIENT_SECRET=your_client_secret
GOSMS_DEFAULT_CHANNEL=1
```

You can get these credentials from your account at [GoSms.cz](https://app.gosms.cz).

## Usage

### Basic Usage

```php
use EcomailGoSms\Client;

// Dependency injection through constructor
public function __construct(Client $goSms)
{
    $this->goSms = $goSms;
}

// Send SMS
$result = $this->goSms->authenticate()->sendSms('+420123456789', 'Hello from Laravel!');
```

### Using with Facade

```php
use EcomailGoSms\Laravel\GoSmsFacade as GoSms;

// Send single SMS
$result = GoSms::authenticate()->sendSms('+420123456789', 'Hello from Laravel!');

// Send multiple SMS at once
$phoneNumbers = ['+420123456789', '+420987654321'];
$result = GoSms::authenticate()->sendMultipleSms($phoneNumbers, 'Bulk message!');
```

### Using with specific channel

```php
$result = GoSms::authenticate()->sendSms('+420123456789', 'Message', 2);
```

## API Reference

### GoSmsClient

#### `authenticate(): GoSmsClient`
Authenticates the client with GoSms.cz API.

#### `sendSms(string $phoneNumber, string $message, ?int $channel = null): stdClass`
Sends SMS message to a single phone number.

#### `sendMultipleSms(array $phoneNumbers, string $message, ?int $channel = null): stdClass`
Sends SMS message to multiple phone numbers at once.

#### `makeRequest(string $type, string $endpoint, ?array $params): stdClass`
Makes a custom HTTP request to GoSms.cz API.

## Exceptions

The package defines the following exceptions:

- `EcomailGoSms\AuthorizationException` - Authentication error
- `EcomailGoSms\InvalidFormat` - Invalid message format
- `EcomailGoSms\InvalidNumber` - Invalid phone number
- `EcomailGoSms\RequestException` - HTTP request error

## Requirements

- PHP 8.1 or higher
- Laravel 9.0 or higher
- Guzzle HTTP Client
- libphonenumber-for-php-lite

## License

MIT License

## Support

For support contact [Ecomail.cz](https://ecomail.cz) or create an issue in this repository.

## Links

- [GoSms.cz API documentation](https://api.gosms.eu/redoc#tag/Messages)
- [Ecomail.cz](https://ecomail.cz)
