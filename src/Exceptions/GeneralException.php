<?php

declare(strict_types = 1);

namespace EcomailGoSms\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function is_int;
use function sprintf;

abstract class GeneralException extends Exception
{

    /** @phpstan-ignore property.unusedType */
    private readonly ?string $responseContents;

    public function __construct(private readonly ResponseInterface $response, ?Throwable $previous = null, ?string $message = null)
    {
        $this->responseContents = $this->response->getBody()->getContents();

        $message ??= $previous?->getMessage() ?? sprintf('"%s"', $this->responseContents);

        // Exception::__construct() takes an int code; a previous exception may carry a string one (PDOException).
        $previousCode = $previous?->getCode();
        $code = is_int($previousCode) ? $previousCode : $this->response->getStatusCode();

        parent::__construct($message, $code, $previous);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @throws \JsonException
     */
    public function getResponseData(): mixed
    {
        return json_decode($this->responseContents, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
    }

}
