<?php

declare(strict_types = 1);

namespace EcomailGoSms\Tests\Unit;

use EcomailGoSms\Exceptions\BadRequest;
use EcomailGoSms\Exceptions\InvalidRequest;
use EcomailGoSms\Message;
use EcomailGoSms\Tests\GoSmsClientTestUtility;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Testing\Assert;
use PHPUnit\Framework\TestCase;

use function fake;
use function file_get_contents;

final class GoSmsClientTest extends TestCase
{

    use GoSmsClientTestUtility;

    public function testSendAsyncMessage(): void
    {
        $response = $this->createGoSmsClientWithJsonResponseAndAccessToken(__DIR__ . '/../Fixtures/send_message_async_success.json');
        $uuid = fake()->uuid();
        $phoneNumber = fake()->e164PhoneNumber();
        $channelId = fake()->randomDigit();
        $message = fake()->text();
        $dto = new Message($message, $channelId, $phoneNumber, $uuid);
        $response = $response->sendMessageAsync($dto);

        self::assertSame('695395e54046d', $response->getCustomId());
        self::assertSame('+420733382412', $response->getRecipient());
        self::assertSame('accepted', $response->getStatus());
        self::assertSame('/api/v2/messages/by-custom-id/695395e54046d', $response->getLink());
    }

    public function testSendAsyncMessages(): void
    {
        $response = $this->createGoSmsClientWithJsonResponseAndAccessToken(__DIR__ . '/../Fixtures/send_messages_async.json');
        $uuid = fake()->uuid();
        $phoneNumber = fake()->e164PhoneNumber();
        $channelId = fake()->randomDigit();
        $message = fake()->text();
        $response = $response->sendMessagesAsync(
            [new Message($message, $channelId, $phoneNumber, $uuid), new Message($message, $channelId, $phoneNumber, $uuid)],
        );

        self::assertSame(2, $response->getTotalAccepted());
        self::assertSame(0, $response->getTotalRejected());
    }

    public function testMessageDetail(): void
    {
        $response = $this->createGoSmsClientWithJsonResponseAndAccessToken(__DIR__ . '/../Fixtures/message_detail.json');
        $uuid = '6953a029b6061';
        $response = $response->getMessageStatistics($uuid);

        self::assertSame($uuid, $response->getCustomId());
        self::assertSame(1, $response->getTotalCount());
        self::assertCount(1, $response->getMessages());
        self::assertSame('processing', $response->getMessages()[0]['status']);
        self::assertSame('+420733382412', $response->getMessages()[0]['recipient']);
        self::assertSame('6953a029b6061', $response->getMessages()[0]['custom_id']);
        self::assertSame('2025-12-30T09:49:29Z', $response->getMessages()[0]['created_at']);
        self::assertSame('2025-12-30T09:49:29Z', $response->getMessages()[0]['updated_at']);
        self::assertNull($response->getMessages()[0]['error']);
    }

    public function testSendAsyncMessageWithExpectedSendStart(): void
    {
        $response = $this->createGoSmsClientWithJsonResponseAndAccessToken(__DIR__ . '/../Fixtures/send_message_async_success.json');
        $uuid = fake()->uuid();
        $phoneNumber = fake()->e164PhoneNumber();
        $channelId = fake()->randomDigit();
        $message = fake()->text();
        $expectedSendStart = fake()->dateTime()->format('Y-m-d H:i:s');
        $dto = new Message($message, $channelId, $phoneNumber, $uuid, $expectedSendStart);
        $response = $response->sendMessageAsync($dto);

        self::assertSame('695395e54046d', $response->getCustomId());
        self::assertSame('+420733382412', $response->getRecipient());
        self::assertSame('accepted', $response->getStatus());
        self::assertSame('/api/v2/messages/by-custom-id/695395e54046d', $response->getLink());
    }

    public function testSendAsyncMessageWithError(): void
    {

        $this->expectException(InvalidRequest::class);

        $response = new Response(422, [], 'Error');
        $request = new Request('POST', 'https://api.gosms.eu/api/v2/messages/');
        $exception = new ClientException('Error', $request, $response);
        $client = $this->createGoSmsClientWithException($exception);
        $uuid = fake()->uuid();
        $phoneNumber = fake()->e164PhoneNumber();
        $channelId = fake()->randomDigit();
        $message = fake()->text();
        $dto = new Message($message, $channelId, $phoneNumber, $uuid);
        $client->sendMessageAsync($dto);
    }

    public function testSendAsyncMessageWithInvalidChannel(): void
    {
        $this->expectException(BadRequest::class);

        $body = (string) file_get_contents(__DIR__ . '/../Fixtures/send_message_async_invalid_channel.json');
        $response = new Response(403, [], $body);
        $request = new Request('POST', 'https://api.gosms.eu/api/v2/messages/');
        $exception = new ClientException('Error', $request, $response);
        $client = $this->createGoSmsClientWithException($exception);
        $uuid = fake()->uuid();
        $phoneNumber = fake()->e164PhoneNumber();
        $channelId = fake()->randomDigit();
        $message = fake()->text();
        $dto = new Message($message, $channelId, $phoneNumber, $uuid);
        $client->sendMessageAsync($dto);
    }

    public function testSendAsyncMessagesWithError(): void
    {
        $this->expectException(InvalidRequest::class);

        $response = new Response(422, [], 'Error');
        $request = new Request('POST', 'https://api.gosms.eu/api/v2/messages/bulk');
        $exception = new ClientException('Error', $request, $response);
        $client = $this->createGoSmsClientWithException($exception);
        $uuid = fake()->uuid();
        $phoneNumber = fake()->e164PhoneNumber();
        $channelId = fake()->randomDigit();
        $message = fake()->text();
        $client->sendMessagesAsync([new Message($message, $channelId, $phoneNumber, $uuid)]);
    }

    public function testSendAsyncMessagesWithInvalidChannel(): void
    {
        $this->expectException(BadRequest::class);

        $body = (string) file_get_contents(__DIR__ . '/../Fixtures/send_message_async_invalid_channel.json');
        $response = new Response(403, [], $body);
        $request = new Request('POST', 'https://api.gosms.eu/api/v2/messages/bulk');
        $exception = new ClientException('Error', $request, $response);
        $client = $this->createGoSmsClientWithException($exception);
        $uuid = fake()->uuid();
        $phoneNumber = fake()->e164PhoneNumber();
        $channelId = fake()->randomDigit();
        $message = fake()->text();
        $client->sendMessagesAsync([new Message($message, $channelId, $phoneNumber, $uuid)]);
    }

    public function testMessageDetailWithError(): void
    {
        $this->expectException(InvalidRequest::class);

        $response = new Response(422, [], 'Error');
        $request = new Request('GET', 'https://api.gosms.eu/api/v2/messages/by-custom-id/test-id');
        $exception = new ClientException('Error', $request, $response);
        $client = $this->createGoSmsClientWithException($exception);
        $client->getMessageStatistics('test-id');
    }

    public function testMessageDetailWithBadRequest(): void
    {
        $this->expectException(BadRequest::class);

        $response = new Response(400, [], 'Error');
        $request = new Request('GET', 'https://api.gosms.eu/api/v2/messages/by-custom-id/test-id');
        $exception = new ClientException('Error', $request, $response);
        $client = $this->createGoSmsClientWithException($exception);
        $client->getMessageStatistics('test-id');
    }

    public function testGenerateSmsId(): void
    {
        $client = $this->createGoSmsClientWithAccessToken();
        Assert::assertNotEmpty($client->generateSmsId());
    }

}
