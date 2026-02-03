<?php

declare(strict_types = 1);

use EcomailGoSms\Message;

require __DIR__ . '/bootstrap.php';

$client = getAuthenticatedClient();
$channelId = getChannelId();

$cliArgs = is_array($_SERVER['argv'] ?? null) ? $_SERVER['argv'] : [];
$recipient = isset($cliArgs[1]) && is_string($cliArgs[1]) ? $cliArgs[1] : null;
$text = isset($cliArgs[2]) && is_string($cliArgs[2]) ? $cliArgs[2] : 'Test SMS from examples.';

if ($recipient === null) {
    echo "Usage: php send-single-message.php <phone_number> [\"<message text>\"]\n";
    echo 'Example: php send-single-message.php ' . EXAMPLES_ALLOWED_RECIPIENT . "\n";
    echo 'Example: php send-single-message.php ' . EXAMPLES_ALLOWED_RECIPIENT . " \"Custom message\"\n";
    exit(1);
}

ensureAllowedRecipient($recipient);

$message = new Message(
    message: $text,
    channelId: $channelId,
    recipient: $recipient,
    customId: 'example-' . uniqid('', true),
);

$response = $client->sendMessageAsync($message);

echo "Message sent.\n";
echo 'Status: ' . $response->getStatus() . "\n";
echo 'Recipient: ' . $response->getRecipient() . "\n";
echo 'Custom ID: ' . $response->getCustomId() . "\n";
echo 'Link: ' . $response->getLink() . "\n";
