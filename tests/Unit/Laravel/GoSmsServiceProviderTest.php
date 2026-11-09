<?php

declare(strict_types = 1);

namespace EcomailGoSms\Tests\Unit\Laravel;

use EcomailGoSms\Client;
use EcomailGoSms\Laravel\GoSmsServiceProvider;
use EcomailGoSms\LaravelGoSmsClient;
use EcomailGoSms\Requests\Request;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

afterEach(function (): void {
    Config::set('gosms.client_id', 'test-client-id');
    Config::set('gosms.client_secret', 'test-client-secret');
    Config::set('gosms.default_channel', 1);
});

it('throws when client id is not string', function (): void {
    Config::set('gosms.client_id');

    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    expect(static fn () => $app->make(Client::class))
        ->toThrow(\InvalidArgumentException::class, 'Configuration value for key [gosms.client_id] must be a string, NULL given.');
});

it('throws when client secret is not string', function (): void {
    Config::set('gosms.client_secret', 123);

    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    expect(static fn () => $app->make(Client::class))
        ->toThrow(\InvalidArgumentException::class, 'Configuration value for key [gosms.client_secret] must be a string, integer given.');
});

it('throws when default channel is not int', function (): void {
    Config::set('gosms.default_channel', '1');

    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    expect(static fn () => $app->make(Client::class))
        ->toThrow(\InvalidArgumentException::class, 'Configuration value for key [gosms.default_channel] must be an integer, string given.');
});

it('throws when default channel is missing', function (): void {
    Config::set('gosms.default_channel');

    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    expect(static fn () => $app->make(Client::class))
        ->toThrow(\InvalidArgumentException::class);
});

it('throws when http client is not http client instance', function (): void {
    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    $app->bind(GoSmsServiceProvider::HTTP_CLIENT, static fn (): \stdClass => new \stdClass());

    expect(static fn () => $app->make(Client::class))
        ->toThrow(\InvalidArgumentException::class, 'Invalid HTTP client instance');
});

it('does not bind the Guzzle client class in the host container', function (): void {
    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    $goSmsHttpClient = $app->make(GoSmsServiceProvider::HTTP_CLIENT);

    expect($app->bound(GuzzleClient::class))->toBeFalse()
        ->and($goSmsHttpClient)->toBeInstanceOf(GuzzleClient::class)
        ->and($goSmsHttpClient)->toBe($app->make(GoSmsServiceProvider::HTTP_CLIENT))
        ->and($app->make(GuzzleClient::class))->not->toBe($goSmsHttpClient);
});

it('sends GoSMS requests through the package http client binding', function (): void {
    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    $mockHandler = new MockHandler([
        new Response(200, [], (string) file_get_contents(__DIR__ . '/../../Fixtures/authenticate.json')),
    ]);
    $app->instance(GoSmsServiceProvider::HTTP_CLIENT, new GuzzleClient(['handler' => HandlerStack::create($mockHandler)]));

    /** @var \EcomailGoSms\LaravelGoSmsClient $client */
    $client = $app->make(Client::class);
    $client->authenticate();

    expect($mockHandler->count())->toBe(0)
        ->and((string) $mockHandler->getLastRequest()?->getUri())->toBe(Request::BASE_URL . Request::API_PATH . '/auth/token');
});

it('resolves client as singleton', function (): void {
    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    $first = $app->make(LaravelGoSmsClient::class);
    $second = $app->make(LaravelGoSmsClient::class);

    expect($first)->toBe($second);
});

it('resolves alias to client', function (): void {
    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    $client = $app->make('gosms');

    expect($client)->toBeInstanceOf(LaravelGoSmsClient::class);
});

it('resolves client with default channel from config', function (): void {
    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    $client = $app->make(Client::class);

    expect($client->getDefaultChannel())->toBe(1);
});

it('publishes config on vendor publish', function (): void {
    $targetPath = config_path('gosms.php');

    if (File::exists($targetPath)) {
        File::delete($targetPath);
    }

    Artisan::call('vendor:publish', [
        '--provider' => GoSmsServiceProvider::class,
        '--tag' => 'config',
        '--force' => true,
    ]);

    expect($targetPath)->toBeFile();
});

it('publishes config via gosms:install command', function (): void {
    $targetPath = config_path('gosms.php');

    if (File::exists($targetPath)) {
        File::delete($targetPath);
    }

    Artisan::call('gosms:install');

    expect($targetPath)->toBeFile();
});

it('provides client and alias', function (): void {
    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    $provider = $app->getProvider(GoSmsServiceProvider::class);

    expect($provider)->toBeInstanceOf(GoSmsServiceProvider::class)
        ->and($provider?->provides())->toBe([Client::class, 'gosms']);
});
