<?php

declare(strict_types=1);

namespace Workflowy\Laravel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Workflowy\WorkflowyClient;

class WorkflowyServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/workflowy.php', 'workflowy'
        );

        $this->app->singleton(WorkflowyClient::class, function ($app) {
            $config = $app['config']['workflowy'];

            if (empty($config['api_key'])) {
                throw new \RuntimeException('Workflowy API key is not defined in config/workflowy.php');
            }

            // Laravel typically has psr implementations available via Guzzle
            // If explicit bindings are missing, we default to Guzzle's implementations
            // which are standard in Laravel deps.
            
            $httpClient = $app->bound(ClientInterface::class) 
                ? $app->make(ClientInterface::class) 
                : new \GuzzleHttp\Client();

            // Guzzle Psr7 Factory usually covers both request and stream factory
            $psr17Factory = class_exists(\GuzzleHttp\Psr7\HttpFactory::class)
                ? new \GuzzleHttp\Psr7\HttpFactory()
                : null;

            $requestFactory = $app->bound(RequestFactoryInterface::class)
                ? $app->make(RequestFactoryInterface::class)
                : $psr17Factory;

            $streamFactory = $app->bound(StreamFactoryInterface::class)
                ? $app->make(StreamFactoryInterface::class)
                : $psr17Factory;

            return new WorkflowyClient(
                apiKey: $config['api_key'],
                httpClient: $httpClient,
                requestFactory: $requestFactory,
                streamFactory: $streamFactory
            );
        });

        $this->app->alias(WorkflowyClient::class, 'workflowy');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/workflowy.php' => config_path('workflowy.php'),
            ], 'workflowy-config');
        }
    }

    public function provides(): array
    {
        return [WorkflowyClient::class, 'workflowy'];
    }
}
