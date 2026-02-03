<?php

declare(strict_types = 1);

namespace EcomailGoSms\Http;

use EcomailGoSms\Exceptions\Request;
use EcomailGoSms\Requests\Request as GoSmsRequest;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

final readonly class GuzzleHttpClient
{

    private const int TIMEOUT = 30;

    private GuzzleClient $guzzleClient;

    /**
     * @param array{base_uri?: string, timeout?: int}|null $config
     */
    public function __construct(?GuzzleClient $guzzleClient = null, ?array $config = null)
    {
        if ($guzzleClient !== null) {
            $this->guzzleClient = $guzzleClient;

            return;
        }

        $baseUri = $config !== null && isset($config['base_uri']) ? $config['base_uri'] : null;
        $timeout = $config !== null && isset($config['timeout']) ? $config['timeout'] : null;

        // @phpstan-ignore cast.string
        $baseUri ??= (string) config('gosms.base_uri', GoSmsRequest::BASE_URL . GoSmsRequest::API_PATH . '/');
        // @phpstan-ignore cast.int
        $timeout ??= (int) config('gosms.timeout', self::TIMEOUT);
        $this->guzzleClient = new GuzzleClient([
            'base_uri' => $baseUri,
            'timeout' => $timeout,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     * @return array{body: array<string, mixed>, status: int}
     * @throws \EcomailGoSms\Exceptions\Request|\JsonException
     */
    public function request(string $method, string $uri, array $data = [], array $headers = []): array
    {
        try {
            $options = $this->buildRequestOptions($method, $data, $headers);
            $response = $this->guzzleClient->request($method, $uri, $options);

            return [
                'body' => $this->decodeResponseBody($response->getBody()->getContents()),
                'status' => $response->getStatusCode(),
            ];
        } catch (GuzzleException $e) {
            throw Request::networkError($e->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     * @return array<string, mixed>
     */
    private function buildRequestOptions(string $method, array $data, array $headers): array
    {
        $options = ['headers' => $headers];

        if ($data !== []) {
            if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'], true)) {
                $options['form_params'] = $data;
            } else {
                $options['query'] = $data;
            }
        }

        return $options;
    }

    /**
     * @return array<string, mixed>
     * @throws \EcomailGoSms\Exceptions\Request
     * @throws \JsonException
     */
    private function decodeResponseBody(string $body): array
    {
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            return [];
        }

        $result = [];

        foreach ($decoded as $key => $value) {
            $result[(string) $key] = $value;
        }

        return $result;
    }

}
