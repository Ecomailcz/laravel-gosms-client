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

    private const array BODY_METHODS = ['POST', 'PUT', 'PATCH'];

    public function __construct(private GuzzleClient $guzzleClient)
    {
    }

    /**
     * @param array{base_uri?: string, timeout?: int} $config
     */
    public static function fromConfig(array $config = []): self
    {
        // @phpstan-ignore cast.string
        $baseUri = $config['base_uri'] ?? (string) config('gosms.base_uri', GoSmsRequest::BASE_URL . GoSmsRequest::API_PATH . '/');
        // @phpstan-ignore cast.int
        $timeout = $config['timeout'] ?? (int) config('gosms.timeout', self::TIMEOUT);

        return new self(new GuzzleClient([
            'base_uri' => $baseUri,
            'timeout' => $timeout,
        ]));
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
            $response = $this->guzzleClient->request($method, $uri, $this->buildRequestOptions($method, $data, $headers));

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
            $key = in_array(strtoupper($method), self::BODY_METHODS, true) ? 'form_params' : 'query';
            $options[$key] = $data;
        }

        return $options;
    }

    /**
     * @return array<string, mixed>
     * @throws \JsonException
     */
    private function decodeResponseBody(string $body): array
    {
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            return [];
        }

        // @phpstan-ignore return.type (json_decode with assoc flag returns string keys for JSON objects)
        return $decoded;
    }

}
