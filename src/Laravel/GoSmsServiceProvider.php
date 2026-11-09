<?php

declare(strict_types = 1);

namespace EcomailGoSms\Laravel;

use EcomailGoSms\Client;
use EcomailGoSms\LaravelGoSmsClient;
use EcomailGoSms\Requests\Request;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

final class GoSmsServiceProvider extends ServiceProvider
{

    /**
     * The container key of the Guzzle client the GoSMS client sends requests with.
     * It is a package-owned key on purpose: binding \GuzzleHttp\Client itself would hand
     * the GoSMS base URI and timeout to every class in the host app that injects a Guzzle client.
     */
    public const string HTTP_CLIENT = 'gosms.http_client';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/gosms.php', 'gosms');

        $this->app->singleton(self::HTTP_CLIENT, static fn (): GuzzleClient => new GuzzleClient([
            'base_uri' => Request::BASE_URL,
            'timeout' => 10,
        ]));

        $this->app->singleton(Client::class, static function (Container $app): LaravelGoSmsClient {
            $publicKey = config()->string('gosms.client_id');
            $privateKey = config()->string('gosms.client_secret');
            $defaultChannel = config()->integer('gosms.default_channel');
            $httpClient = $app->make(self::HTTP_CLIENT);

            if (!$httpClient instanceof GuzzleClient) {
                throw new \InvalidArgumentException('Invalid HTTP client instance');
            }

            return new LaravelGoSmsClient(
                $publicKey,
                $privateKey,
                null,
                $defaultChannel,
                'password',
                '',
                $httpClient,
            );
        });

        $this->app->alias(Client::class, LaravelGoSmsClient::class);
        $this->app->alias(LaravelGoSmsClient::class, 'gosms');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/gosms.php' => config_path('gosms.php'),
        ], ['config', 'gosms-config']);

        if ($this->app->runningInConsole()) {
            $this->commands([InstallGoSmsCommand::class]);
        }
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [Client::class, 'gosms'];
    }

}
