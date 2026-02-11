<?php

declare(strict_types = 1);

namespace EcomailGoSms\Tests\Unit\Responses;

use EcomailGoSms\Exceptions\InvalidResponseData;
use EcomailGoSms\Responses\MessageStatusResponse;
use EcomailGoSms\Tests\GoSmsClientTestUtility;
use Mockery;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

final class MessageStatusResponseTest extends TestCase
{

    use GoSmsClientTestUtility;

    public function testGetCustomId(): void
    {
        $response = $this->createMessageStatusResponse();

        self::assertSame('6953a029b6061', $response->getCustomId());
    }

    public function testGetTotalCount(): void
    {
        $response = $this->createMessageStatusResponse();

        self::assertSame(1, $response->getTotalCount());
    }

    public function testGetMessages(): void
    {
        $response = $this->createMessageStatusResponse();
        $messages = $response->getMessages();

        self::assertCount(1, $messages);
        self::assertSame('processing', $messages[0]['status']);
        self::assertSame('+420733382412', $messages[0]['recipient']);
        self::assertSame('6953a029b6061', $messages[0]['custom_id']);
        self::assertSame('2025-12-30T09:49:29Z', $messages[0]['created_at']);
        self::assertSame('2025-12-30T09:49:29Z', $messages[0]['updated_at']);
        self::assertNull($messages[0]['error']);
    }

    public function testValidationError(): void
    {
        $response = $this->createMessageStatusInvalidResponse();
        $this->expectException(InvalidResponseData::class);
        $response->getCustomId();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    private function createMessageStatusResponse(): MessageStatusResponse
    {
        $json = (string) file_get_contents(__DIR__ . '/../../Fixtures/message_detail.json');
        $mockResponse = $this->createMockResponseWithBody($json);

        return new MessageStatusResponse($mockResponse);
    }

    private function createMessageStatusInvalidResponse(): MessageStatusResponse
    {
        $mockResponse = $this->createMockResponseWithBody('{}', statusCode: 404);

        return new MessageStatusResponse($mockResponse);
    }

}
