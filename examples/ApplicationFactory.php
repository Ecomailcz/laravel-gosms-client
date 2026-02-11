<?php

declare(strict_types = 1);

use EcomailGoSms\LaravelGoSmsClient;
use EcomailGoSms\Tests\TestCase;
use Illuminate\Foundation\Application;

final class ApplicationFactory extends TestCase
{

    public function createApp(): Application
    {
        return $this->createApplication();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $app['config'];

        $config->set('gosms.client_id', $this->envString('GOSMS_CLIENT_ID'));
        $config->set('gosms.client_secret', $this->envString('GOSMS_CLIENT_SECRET'));

        $channelId = $this->envChannelId();

        if ($channelId !== null) {
            $config->set('gosms.default_channel', $channelId);
        }

        $app->singleton('gosms.authenticated', static function () use ($app): LaravelGoSmsClient {
            $client = $app->make(LaravelGoSmsClient::class);

            return $client->authenticate();
        });
    }

    private function envString(string $key): string
    {
        $value = $_ENV[$key] ?? null;

        return isset($value) && is_string($value) ? $value : '';
    }

    private function envChannelId(): ?int
    {
        $raw = $_ENV['GOSMS_CHANNEL_ID'] ?? null;

        if (!is_scalar($raw)) {
            return null;
        }

        $str = (string) $raw;

        return $str !== '' && ctype_digit($str) ? (int) $str : null;
    }

}
