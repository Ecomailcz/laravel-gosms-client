# Ecomail GoSms

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen.svg)](https://phpstan.org/)
[![Pest](https://img.shields.io/badge/Pest-v4-f472b6.svg)](https://pestphp.com/)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)](https://github.com/Ecomailcz/laravel-gosms-client)

Laravel package for the [GoSms.cz](https://gosms.cz) API. Send single or bulk SMS, verify connection, and check message status via the `GoSms` facade.

---

## Installation

**1. Install the package**

```bash
composer require ecomailcz/laravel-gosms-client
```

**2. Publish config and get `.env` snippet**

```bash
php artisan gosms:install
```

This creates `config/gosms.php` and prints the variables to add to `.env`.

**3. Add credentials to `.env`**

```env
GOSMS_CLIENT_ID=your_client_id
GOSMS_CLIENT_SECRET=your_client_secret
GOSMS_DEFAULT_CHANNEL=1
```

The package auto-registers (Laravel package discovery). No provider or alias registration needed.

**Alternative:** publish config only: `php artisan vendor:publish --tag=gosms-config`

---

## Usage

```php
use EcomailGoSms\Laravel\GoSmsFacade as GoSms;

// Verify connection
GoSms::authenticate();

// Send one SMS
GoSms::sendMessageAsync('+420123456789', 'Message text');

// Send to multiple numbers (same text)
GoSms::sendMessagesAsync(['+420123456789', '+420987654321'], 'Shared text');
```

More examples: [examples/README.md](examples/README.md).

---

## Requirements

- PHP 8.4+
- Laravel 12.x
- Composer 2.x

---

## License

[MIT](LICENSE).

---

## Author

**Petr Král**

- Contributions welcome, see [CONTRIBUTING.md](CONTRIBUTING.md).
- GitHub: [@pekral](https://github.com/pekral)
- Email: kral.petr.88@gmail.com
- X (Twitter): [https://x.com/kral_petr_88](https://x.com/kral_petr_88)
