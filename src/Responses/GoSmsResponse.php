<?php

declare(strict_types = 1);

namespace EcomailGoSms\Responses;

use EcomailGoSms\Exceptions\InvalidResponseData;
use JsonException;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;

use function gettype;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;
use function json_encode;
use function sprintf;

abstract class GoSmsResponse implements JsonSerializable
{

    private readonly string $responseBody;

    public function __construct(private readonly ResponseInterface $response)
    {
        $this->responseBody = $response->getBody()->getContents();
    }

    /**
     * @return array<string, mixed>
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     */
    public function toArray(): array
    {
        return $this->decodeResponseBody();
    }

    /**
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     * @throws \JsonException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     * @throws \JsonException
     */
    protected function getStringByKey(string $key): string
    {
        // @phpstan-ignore return.type (validated by callable)
        return $this->getValueByKey($key, is_string(...));
    }

    /**
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     * @throws \JsonException
     */
    protected function getIntegerByKey(string $key): int
    {
        // @phpstan-ignore return.type (validated by callable)
        return $this->getValueByKey($key, is_int(...));
    }

    /**
     * @return array<string|int, mixed>
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     * @throws \JsonException
     */
    protected function getArrayByKey(string $key): array
    {
        // @phpstan-ignore return.type (validated by callable)
        return $this->getValueByKey($key, is_array(...));
    }

    /**
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     */
    protected function getDataByKey(string $key): mixed
    {
        return $this->decodeResponseBody()[$key] ?? null;
    }

    /**
     * @param callable(mixed): bool $typeCheck
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     * @throws \JsonException
     */
    private function getValueByKey(string $key, callable $typeCheck): mixed
    {
        $value = $this->getDataByKey($key);

        if (!$typeCheck($value)) {
            throw new InvalidResponseData($this->response, message: $this->formatInvalidDataMessage($key, $value));
        }

        return $value;
    }

    /**
     * @return array<string, mixed>
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     */
    private function decodeResponseBody(): array
    {
        try {
            /** @var array<string, mixed>|null $decoded */
            $decoded = json_decode($this->responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new InvalidResponseData($this->response);
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @throws \JsonException
     */
    private function formatInvalidDataMessage(string $key, mixed $value): string
    {
        return sprintf(
            'Invalid response data for key %s. Value is %s with type %s',
            $key,
            json_encode($value, JSON_THROW_ON_ERROR),
            gettype($value),
        );
    }

}
