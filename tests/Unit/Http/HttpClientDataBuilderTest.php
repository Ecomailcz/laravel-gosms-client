<?php

declare(strict_types = 1);

use EcomailGoSms\Http\HttpClientDataBuilder;

it('builds options with headers only when no data', function (): void {
    $builder = new HttpClientDataBuilder();
    $result = $builder->build('GET', [], ['X-Custom' => 'value']);

    expect($result)->toBe(['headers' => ['X-Custom' => 'value']]);
});

it('builds options with query for GET', function (): void {
    $builder = new HttpClientDataBuilder();
    $result = $builder->build('GET', ['key' => 'value'], []);

    expect($result)->toBe([
        'headers' => [],
        'query' => ['key' => 'value'],
    ]);
});

it('builds options with form_params for POST', function (): void {
    $builder = new HttpClientDataBuilder();
    $result = $builder->build('POST', ['client_id' => 'id'], []);

    expect($result)->toBe([
        'headers' => [],
        'form_params' => ['client_id' => 'id'],
    ]);
});

it('builds options with form_params for PUT', function (): void {
    $builder = new HttpClientDataBuilder();
    $result = $builder->build('PUT', ['field' => 'data'], []);

    expect($result['form_params'])->toBe(['field' => 'data']);
});

it('builds options with form_params for PATCH', function (): void {
    $builder = new HttpClientDataBuilder();
    $result = $builder->build('PATCH', ['field' => 'data'], []);

    expect($result['form_params'])->toBe(['field' => 'data']);
});

it('decodes valid json object to array', function (): void {
    $builder = new HttpClientDataBuilder();
    $result = $builder->decodeResponseBody('{"access_token":"token","token_type":"Bearer"}');

    expect($result)->toBe(['access_token' => 'token', 'token_type' => 'Bearer']);
});

it('returns empty array for null json', function (): void {
    $builder = new HttpClientDataBuilder();
    $result = $builder->decodeResponseBody('null');

    expect($result)->toBe([]);
});

it('throws on invalid json', function (): void {
    $builder = new HttpClientDataBuilder();

    expect(fn (): array => $builder->decodeResponseBody('invalid json'))->toThrow(JsonException::class);
});

it('normalizes keys to string', function (): void {
    $builder = new HttpClientDataBuilder();
    $result = $builder->decodeResponseBody('{"0":"zero","1":"one"}');

    expect($result)->toBe(['0' => 'zero', '1' => 'one']);
});
