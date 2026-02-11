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

    private const string CONFIG_PATH = __DIR__ . '/../../config/gosms.php';

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'gosms');
        $this->registerHttpClient();
        $this->registerGoSmsClient();
        $this->registerAliases();
    }

    public function boot(): void
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('gosms.php'),
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

    private function registerHttpClient(): void
    {
        $this->app->singleton(GuzzleClient::class, static fn (): GuzzleClient => new GuzzleClient([
            'base_uri' => Request::BASE_URL,
            'timeout' => 10,
        ]));
    }

    private function registerGoSmsClient(): void
    {
        $this->app->singleton(Client::class, static function (Container $app): LaravelGoSmsClient {
            $httpClient = $app->make(GuzzleClient::class);

            /** @phpstan-ignore instanceof.alwaysTrue (binding can be overridden in tests) */
            if (!$httpClient instanceof GuzzleClient) {
                throw new \InvalidArgumentException('Invalid HTTP client instance');
            }

            return new LaravelGoSmsClient(
                config()->string('gosms.client_id'),
                config()->string('gosms.client_secret'),
                null,
                config()->integer('gosms.default_channel'),
                'password',
                '',
                $httpClient,
            );
        });
    }

    private function registerAliases(): void
    {
        $this->app->alias(Client::class, LaravelGoSmsClient::class);
        $this->app->alias(LaravelGoSmsClient::class, 'gosms');
    }

}
