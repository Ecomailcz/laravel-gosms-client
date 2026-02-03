<?php

declare(strict_types = 1);

namespace EcomailGoSms\Tests\Laravel;

use EcomailGoSms\Client;
use EcomailGoSms\Laravel\GoSmsServiceProvider;
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
        ->toThrow(\InvalidArgumentException::class, 'Invalid GoSms configuration');
});

it('throws when client secret is not string', function (): void {
    Config::set('gosms.client_secret', 123);

    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    expect(static fn () => $app->make(Client::class))
        ->toThrow(\InvalidArgumentException::class, 'Invalid GoSms configuration');
});

it('throws when default channel is not int', function (): void {
    Config::set('gosms.default_channel', '1');

    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    expect(static fn () => $app->make(Client::class))
        ->toThrow(\InvalidArgumentException::class, 'Invalid GoSms configuration');
});

it('throws when http client is not http client instance', function (): void {
    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    $app->bind(\EcomailGoSms\Http\GuzzleHttpClient::class, static fn (): \stdClass => new \stdClass());

    expect(static fn () => $app->make(Client::class))
        ->toThrow(\InvalidArgumentException::class, 'Invalid HTTP client instance');
});

it('resolves client as singleton', function (): void {
    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    $first = $app->make(Client::class);
    $second = $app->make(Client::class);

    expect($first)->toBe($second);
});

it('resolves alias to client', function (): void {
    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    $client = $app->make('gosms');

    expect($client)->toBeInstanceOf(Client::class);
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

it('provides client and alias', function (): void {
    /** @var \Illuminate\Foundation\Application $app */
    $app = app();

    $provider = $app->getProvider(GoSmsServiceProvider::class);

    expect($provider)->toBeInstanceOf(GoSmsServiceProvider::class)
        ->and($provider?->provides())->toBe([Client::class, 'gosms']);
});
