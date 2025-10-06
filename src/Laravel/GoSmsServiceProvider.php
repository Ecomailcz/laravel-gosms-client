<?php

declare(strict_types = 1);

namespace EcomailGoSms\Laravel;

use EcomailGoSms\Client;
use EcomailGoSms\Contracts\HttpClient;
use EcomailGoSms\Http\GuzzleHttpClient;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

final class GoSmsServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/gosms.php', 'gosms');

        $this->app->singleton(Client::class, static function (Container $app): Client {
            $clientId = Config::get('gosms.client_id');
            $clientSecret = Config::get('gosms.client_secret');
            $defaultChannel = Config::get('gosms.default_channel');
            
            if (!is_string($clientId) || !is_string($clientSecret) || !is_int($defaultChannel)) {
                throw new \InvalidArgumentException('Invalid GoSms configuration');
            }
            
            $httpClient = $app->make(GuzzleHttpClient::class);
            
            if (!$httpClient instanceof HttpClient) {
                throw new \InvalidArgumentException('Invalid HTTP client instance');
            }
            
            return new Client(
                $clientId,
                $clientSecret,
                $defaultChannel,
                $httpClient,
            );
        });

        $this->app->alias(Client::class, 'gosms');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/gosms.php' => config_path('gosms.php'),
        ], 'config');
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [Client::class, 'gosms'];
    }

}
