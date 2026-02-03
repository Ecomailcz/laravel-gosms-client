<?php

declare(strict_types = 1);

namespace EcomailGoSms\Http;

final readonly class HttpClientDataBuilder
{

    private const array FORM_PARAM_METHODS = ['POST', 'PUT', 'PATCH'];

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     * @return array<string, mixed>
     */
    public function build(string $method, array $data, array $headers): array
    {
        $options = ['headers' => $headers];

        if ($data !== []) {
            if (in_array(strtoupper($method), self::FORM_PARAM_METHODS, true)) {
                $options['form_params'] = $data;
            } else {
                $options['query'] = $data;
            }
        }

        return $options;
    }

    /**
     * @return array<string, mixed>
     * @throws \JsonException
     */
    public function decodeResponseBody(string $body): array
    {
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            return [];
        }

        return array_combine(
            array_map('strval', array_keys($decoded)),
            array_values($decoded),
        );
    }

}
