# Moderní architektura Client::class

## Přehled návrhu

Navrhl jsem čistou a moderní architekturu pro GoSms API Client s následujícími principy:

### 1. **Dependency Injection**
```php
public function __construct(
    private readonly string $clientId,
    private readonly string $clientSecret,
    private readonly int $defaultChannel,
    private readonly HttpClientInterface $httpClient,
    private readonly PhoneNumberValidator $phoneValidator = new PhoneNumberValidator(),
) {
}
```

**Výhody:**
- Snadné testování pomocí mocků
- Flexibilní výměna implementací
- Žádné hard dependencies

### 2. **Contract-based Design**
- `HttpClientInterface` - abstrakce HTTP komunikace
- Umožňuje snadnou výměnu Guzzle za jinou HTTP knihovnu
- Jednodušší mockování v testech

### 3. **Type Safety**
- PHP 8.4 typed properties
- Constructor property promotion
- Spatie Laravel Data pro request/response DTOs
- PHPStan level 9 ready

### 4. **Strukturované Exceptions**
```
GoSmsException (abstract)
├── AuthorizationException
├── InvalidFormatException
├── InvalidNumberException
└── RequestException
```

### 5. **Data Objects (DTOs)**
- `AuthRequest` / `AuthResponse`
- `SmsRequest` / `SmsResponse`
- `BulkSmsRequest` / `BulkSmsResponse`

**Výhody:**
- Typově bezpečné
- Automatická validace
- Snadná transformace do/z array

### 6. **Phone Number Validation**
- Využití `libphonenumber-for-php-lite`
- Automatické formátování do E.164
- Validace českých i mezinárodních čísel

## Struktura tříd

```
src/
├── Client.php (hlavní třída)
├── Contracts/
│   └── HttpClientInterface.php
├── Data/
│   ├── AuthRequest.php
│   ├── AuthResponse.php
│   ├── SmsRequest.php
│   ├── SmsResponse.php
│   ├── BulkSmsRequest.php
│   └── BulkSmsResponse.php
├── Exceptions/
│   ├── GoSmsException.php
│   ├── AuthorizationException.php
│   ├── InvalidFormatException.php
│   ├── InvalidNumberException.php
│   └── RequestException.php
├── Http/
│   └── GuzzleHttpClient.php
├── Validation/
│   └── PhoneNumberValidator.php
└── Laravel/
    ├── GoSmsServiceProvider.php
    └── GoSmsFacade.php
```

## Klíčové vlastnosti

### 1. Fluent Interface
```php
$client->authenticate()->sendSms('+420123456789', 'Hello!');
```

### 2. Automatická Validace
- Validace formátu zprávy (max 160 znaků)
- Validace telefonních čísel
- Kontrola autentizace před každým požadavkem

### 3. Čitelné Error Messages
```php
// Místo generických chyb
AuthorizationException::invalidCredentials()
InvalidFormatException::invalidMessageFormat('Message too long')
InvalidNumberException::invalidPhoneNumber('+420...')
```

### 4. 100% Test Coverage
- Unit testy pro všechny třídy
- Mockování externích závislostí
- Pest testing framework ready

## Použití

### Základní použití
```php
$client = new Client(
    'client_id',
    'client_secret',
    1, // default channel
    new GuzzleHttpClient()
);

$client->authenticate()
    ->sendSms('+420123456789', 'Hello from GoSms!');
```

### Laravel použití
```php
use EcomailGoSms\Laravel\GoSmsFacade as GoSms;

GoSms::authenticate()
    ->sendSms('+420123456789', 'Hello!');
```

### Hromadné odesílání
```php
$result = GoSms::authenticate()
    ->sendMultipleSms(
        ['+420111111111', '+420222222222'],
        'Bulk message'
    );

echo "Success: {$result->successCount}, Errors: {$result->errorCount}";
```

## Testovatelnost

### Příklad testu
```php
$httpClient = Mockery::mock(HttpClientInterface::class);
$phoneValidator = new PhoneNumberValidator();

$client = new Client(
    'test_id',
    'test_secret',
    1,
    $httpClient,
    $phoneValidator
);

// Mock HTTP response
$httpClient->shouldReceive('request')
    ->once()
    ->andReturn([
        'status' => 200,
        'body' => ['access_token' => 'token123']
    ]);

$client->authenticate();
```

## Best Practices použité

✅ **Single Responsibility Principle** - každá třída má jednu zodpovědnost
✅ **Dependency Inversion** - závislosti přes interface
✅ **Open/Closed Principle** - rozšiřitelné bez modifikace
✅ **Interface Segregation** - malé, fokusované interface
✅ **Liskov Substitution** - implementace jsou zaměnitelné

## Moderní PHP 8.4 Features

- Constructor property promotion
- Readonly properties
- Typed properties
- Named arguments support
- Match expressions (v HTTP client)
- Null safe operator

## Výhody pro programátora

1. **Čitelný kód** - expresivní metody, jasné názvy
2. **Snadné testování** - všechno je mockovatelné
3. **Type hints** - IDE autocomplete, méně chyb
4. **Dokumentovaný** - PHPDoc s typed arrays
5. **Extendovatelný** - přidání nových funkcí bez změny existujících
6. **Laravel integrovaný** - Service provider, facade, config

## Závěr

Tato architektura poskytuje:
- ✨ Moderní, čistý kód
- 🧪 100% testovatelnost
- 📚 Snadná čitelnost
- 🔒 Type safety
- 🚀 Jednoduchost použití
- 🔧 Snadná údržba

