<?php

declare(strict_types = 1);

use EcomailGoSms\Exceptions\Request as GoSmsRequestException;
use EcomailGoSms\Http\GuzzleHttpClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;

beforeEach(function (): void {
    Config::set('gosms.base_uri', 'http://127.0.0.1:19999/');
    Config::set('gosms.timeout', 30);
});

it('fromConfig uses passed config values', function (): void {
    $client = GuzzleHttpClient::fromConfig([
        'base_uri' => 'http://127.0.0.1:19999/',
        'timeout' => 15,
    ]);

    expect(fn (): array => $client->request('GET', 'test'))->toThrow(GoSmsRequestException::class);
});

it('fromConfig falls back to laravel config', function (): void {
    Config::set('gosms.base_uri', 'http://127.0.0.1:19999/');
    Config::set('gosms.timeout', 15);

    $client = GuzzleHttpClient::fromConfig();

    expect(fn (): array => $client->request('GET', 'test'))->toThrow(GoSmsRequestException::class);
});

it('fromConfig falls back to laravel config for missing keys', function (): void {
    Config::set('gosms.timeout', 20);

    $client = GuzzleHttpClient::fromConfig(['base_uri' => 'http://127.0.0.1:19999/']);

    expect(fn (): array => $client->request('GET', 'test'))->toThrow(GoSmsRequestException::class);
});

it('request returns body and status', function (): void {
    $mock = new MockHandler([
        new Response(200, [], '{"access_token":"token","refresh_token":"refresh","token_type":"Bearer"}'),
    ]);
    $guzzleClient = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
    $client = new GuzzleHttpClient($guzzleClient);

    $result = $client->request('POST', 'auth/token', ['client_id' => 'id', 'client_secret' => 'secret']);

    expect($result['status'])->toBe(200)
        ->and($result['body']['access_token'])->toBe('token')
        ->and($result['body']['refresh_token'])->toBe('refresh')
        ->and($result['body']['token_type'])->toBe('Bearer');
});

it('request throws on network error', function (): void {
    $mock = new MockHandler([
        new ConnectException('Connection refused', new Request('POST', 'auth/token')),
    ]);
    $guzzleClient = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
    $client = new GuzzleHttpClient($guzzleClient);

    expect(fn (): array => $client->request('POST', 'auth/token'))
        ->toThrow(GoSmsRequestException::class, 'Network error: Connection refused');
});

it('request throws on invalid json', function (): void {
    $mock = new MockHandler([new Response(200, [], 'invalid json')]);
    $guzzleClient = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
    $client = new GuzzleHttpClient($guzzleClient);

    expect(fn (): array => $client->request('GET', 'messages'))->toThrow(JsonException::class);
});

it('request get with query params', function (): void {
    $mock = new MockHandler([
        new Response(200, [], '{"custom_id":"id-1","total_count":0,"messages":[]}'),
    ]);
    $guzzleClient = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
    $client = new GuzzleHttpClient($guzzleClient);

    $result = $client->request('GET', 'messages/by-custom-id/id-1', ['custom_id' => 'id-1']);

    expect($result['status'])->toBe(200)
        ->and($result['body']['custom_id'])->toBe('id-1')
        ->and($result['body']['total_count'])->toBe(0);
});

it('request returns empty array when response body is null json', function (): void {
    $mock = new MockHandler([new Response(200, [], 'null')]);
    $guzzleClient = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
    $client = new GuzzleHttpClient($guzzleClient);

    $result = $client->request('GET', 'messages');

    expect($result['status'])->toBe(200)->and($result['body'])->toBe([]);
});
