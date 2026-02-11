<?php

declare(strict_types = 1);

use EcomailGoSms\LaravelGoSmsClient;
use EcomailGoSms\Messages\Sms;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/helpers.php';

$app = getApplication();
$client = $app->make('gosms');

assert($client instanceof LaravelGoSmsClient);

$client->authenticate();
$channelId = getChannelId();

$cliArgs = $_SERVER['argv'] ?? [];
/** @var list<string> $recipients */
$recipients = array_slice(is_array($cliArgs) ? $cliArgs : [], 1);

if ($recipients === []) {
    echo "Usage: php bulk-send-and-wait-for-sent.php <phone1> [phone2] ...\n";
    echo 'Example: php bulk-send-and-wait-for-sent.php ' . EXAMPLES_ALLOWED_RECIPIENT . "\n";
    echo 'Examples may only send to ' . EXAMPLES_ALLOWED_RECIPIENT . ".\n";
    exit(1);
}

$messages = collect($recipients)->map(static fn (string $recipient): Sms => new Sms(
    message: 'Bulk test SMS – waiting for sent status. ' . uniqid(more_entropy: true),
    channelId: $channelId,
    recipient: $recipient,
    customId: uniqid(more_entropy: true),
));
$response = $client->sendMessagesAsync($messages->toArray());

echo "\n";
echo "=== Bulk Send Results ===\n";
echo sprintf("  Accepted: %d\n", $response->getTotalAccepted());
echo sprintf("  Rejected: %d\n", $response->getTotalRejected());

$accepted = $response->getAccepted();

if ($accepted !== []) {
    echo "\nAccepted messages:\n";
    printTable($accepted);
}

$rejected = $response->getRejected();

if ($rejected !== []) {
    echo "\nRejected messages:\n";
    printTable($rejected);
}

echo "\nWaiting 5 seconds for delivery...\n";
sleep(5);

foreach ($accepted as $sent) {
    $status = $client->getMessageStatistics($sent['custom_id']);

    echo sprintf("\n=== Message Status (custom_id: %s, total: %d) ===\n", $status->getCustomId(), $status->getTotalCount());
    printTable($status->getMessages());
}
