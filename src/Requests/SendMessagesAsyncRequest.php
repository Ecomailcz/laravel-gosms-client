<?php

declare(strict_types = 1);

namespace EcomailGoSms\Requests;

use EcomailGoSms\Messages\Sms;

use function collect;

final readonly class SendMessagesAsyncRequest implements Request
{

    /**
     * @param array<\EcomailGoSms\Messages\Sms> $messages
     */
    public function __construct(private array $messages)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        $messages = collect($this->messages)
            ->map(static fn (Sms $message): array => $message->toArray());

        return [
            'json' => ['messages' => $messages->toArray()],
        ];
    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getEndpoint(): string
    {
        return self::BASE_URL . self::API_PATH . '/messages/bulk';
    }

}
