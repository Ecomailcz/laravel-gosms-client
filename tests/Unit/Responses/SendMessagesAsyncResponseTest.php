<?php

declare(strict_types = 1);

namespace EcomailGoSms\Tests\Unit\Responses;

use EcomailGoSms\Exceptions\InvalidResponseData;
use EcomailGoSms\Responses\SendMessagesAsyncResponse;
use EcomailGoSms\Tests\GoSmsClientTestUtility;
use Mockery;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

final class SendMessagesAsyncResponseTest extends TestCase
{

    use GoSmsClientTestUtility;

    public function testGetAccepted(): void
    {
        $response = $this->createSendMessagesAsyncResponse();
        $accepted = $response->getAccepted();

        self::assertCount(2, $accepted);
        self::assertSame('accepted', $accepted[0]->status);
        self::assertSame('+420733382412', $accepted[0]->recipient);
        self::assertSame('6953ab3ad4eb3', $accepted[0]->customId);
        self::assertSame('/api/v2/messages/by-custom-id/6953ab3ad4eb3', $accepted[0]->link);
        self::assertSame('accepted', $accepted[1]->status);
        self::assertSame('+420733382412', $accepted[1]->recipient);
        self::assertSame('6953ab3ad4eb5', $accepted[1]->customId);
        self::assertSame('/api/v2/messages/by-custom-id/6953ab3ad4eb5', $accepted[1]->link);
    }

    public function testGetRejected(): void
    {
        $response = $this->createSendMessagesAsyncResponse();
        $rejected = $response->getRejected();

        self::assertCount(0, $rejected);
    }

    public function testGetRejectedWithData(): void
    {
        $response = $this->createSendMessagesAsyncWithRejectedResponse();
        $rejected = $response->getRejected();

        self::assertCount(1, $rejected);
        self::assertSame('rejected', $rejected[0]->status);
        self::assertSame('+420000000000', $rejected[0]->recipient);
        self::assertSame('6953ab3ad4eb9', $rejected[0]->customId);
        self::assertSame('/api/v2/messages/by-custom-id/6953ab3ad4eb9', $rejected[0]->link);
    }

    public function testGetAcceptedWithRejectedMix(): void
    {
        $response = $this->createSendMessagesAsyncWithRejectedResponse();
        $accepted = $response->getAccepted();

        self::assertCount(1, $accepted);
        self::assertSame('accepted', $accepted[0]->status);
        self::assertSame('+420733382412', $accepted[0]->recipient);
        self::assertSame('6953ab3ad4eb3', $accepted[0]->customId);
        self::assertSame('/api/v2/messages/by-custom-id/6953ab3ad4eb3', $accepted[0]->link);
    }

    public function testGetTotalAccepted(): void
    {
        $response = $this->createSendMessagesAsyncResponse();

        self::assertSame(2, $response->getTotalAccepted());
    }

    public function testGetTotalRejected(): void
    {
        $response = $this->createSendMessagesAsyncResponse();

        self::assertSame(0, $response->getTotalRejected());
    }

    public function testGetTotalAcceptedWithRejectedMix(): void
    {
        $response = $this->createSendMessagesAsyncWithRejectedResponse();

        self::assertSame(1, $response->getTotalAccepted());
    }

    public function testGetTotalRejectedWithRejectedMix(): void
    {
        $response = $this->createSendMessagesAsyncWithRejectedResponse();

        self::assertSame(1, $response->getTotalRejected());
    }

    public function testValidationError(): void
    {
        $response = $this->createSendMessagesAsyncInvalidResponse();
        $this->expectException(InvalidResponseData::class);
        $response->getAccepted();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    private function createSendMessagesAsyncResponse(): SendMessagesAsyncResponse
    {
        $json = (string) file_get_contents(__DIR__ . '/../../Fixtures/send_messages_async.json');
        $mockResponse = $this->createMockResponseWithBody($json);

        return new SendMessagesAsyncResponse($mockResponse);
    }

    private function createSendMessagesAsyncWithRejectedResponse(): SendMessagesAsyncResponse
    {
        $json = (string) file_get_contents(__DIR__ . '/../../Fixtures/send_messages_async_with_rejected.json');
        $mockResponse = $this->createMockResponseWithBody($json);

        return new SendMessagesAsyncResponse($mockResponse);
    }

    private function createSendMessagesAsyncInvalidResponse(): SendMessagesAsyncResponse
    {
        $json = (string) file_get_contents(__DIR__ . '/../../Fixtures/send_messages_async_validation_error.json');
        $mockResponse = $this->createMockResponseWithBody($json, statusCode: 422);

        return new SendMessagesAsyncResponse($mockResponse);
    }

}
