<?php

declare(strict_types = 1);

namespace EcomailGoSms\Http;

use EcomailGoSms\Exceptions\Request;
use EcomailGoSms\Requests\Request as GoSmsRequest;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

final readonly class GuzzleHttpClient
{

    private GuzzleClient $guzzleClient;

    /**
     * @param array{base_uri?: string, timeout?: int}|null $config
     */
    public function __construct(
        ?GuzzleClient $guzzleClient = null,
        ?array $config = null,
        private HttpClientDataBuilder $dataBuilder = new HttpClientDataBuilder(),
    ) {
        $this->guzzleClient = $guzzleClient ?? new GuzzleClient($this->resolveConfig($config));
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     * @return array{
     *   body: array<string, mixed>,
     *   status: int
     * }
     * @throws \EcomailGoSms\Exceptions\Request|\JsonException
     */
    public function request(string $method, string $uri, array $data = [], array $headers = []): array
    {
        try {
            $options = $this->dataBuilder->build($method, $data, $headers);
            $response = $this->guzzleClient->request($method, $uri, $options);

            return [
                'body' => $this->dataBuilder->decodeResponseBody($response->getBody()->getContents()),
                'status' => $response->getStatusCode(),
            ];
        } catch (GuzzleException $e) {
            throw Request::networkError($e->getMessage());
        }
    }

    /**
     * @param array{base_uri?: string, timeout?: int}|null $config
     * @return array{base_uri: string, timeout?: int}
     */
    private function resolveConfig(?array $config): array
    {
        $configArray = $config ?? [];
        $baseUri = $configArray['base_uri'] ?? null;
        $timeout = $configArray['timeout'] ?? config('gosms.timeout');
        // @phpstan-ignore cast.string
        $baseUri ??= (string) config('gosms.base_uri', GoSmsRequest::BASE_URL . GoSmsRequest::API_PATH . '/');

        $guzzleConfig = ['base_uri' => $baseUri];

        if ($timeout !== null) {
            // @phpstan-ignore cast.int
            $guzzleConfig['timeout'] = (int) $timeout;
        }

        return $guzzleConfig;
    }

}
