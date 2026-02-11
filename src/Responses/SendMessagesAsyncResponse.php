<?php

declare(strict_types = 1);

namespace EcomailGoSms\Responses;

final class SendMessagesAsyncResponse extends GoSmsResponse
{

    /**
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     * @throws \JsonException
     * @return array<int|string, mixed>
     */
    public function getAccepted(): array
    {
        return $this->getArrayByKey('accepted');
    }

    /**
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     * @throws \JsonException
     * @return array<int|string, mixed>
     */
    public function getRejected(): array
    {
        return $this->getArrayByKey('rejected');
    }

    /**
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     * @throws \JsonException
     */
    public function getTotalAccepted(): int
    {
        return $this->getIntegerByKey('total_accepted');
    }

    /**
     * @throws \EcomailGoSms\Exceptions\InvalidResponseData
     * @throws \JsonException
     */
    public function getTotalRejected(): int
    {
        return $this->getIntegerByKey('total_rejected');
    }

}
