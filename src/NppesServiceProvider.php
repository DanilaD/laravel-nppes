<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES;

use DolmatovDev\NPPES\Contracts\NpiLookupInterface;
use DolmatovDev\NPPES\Services\NpiApiClient;
use DolmatovDev\NPPES\Services\NpiCacheService;
use DolmatovDev\NPPES\Services\NpiLookupService;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class NppesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/nppes.php', 'nppes'
        );

        $this->app->singleton('nppes.http-client', function () {
            return HttpClient::create([
                'timeout' => config('nppes.api.timeout', 30),
                'max_redirects' => 3,
                'verify_peer' => true,
                'verify_host' => true,
            ]);
        });

        $this->app->singleton('nppes.cache-adapter', function () {
            $driver = config('nppes.cache.driver', 'array');
            
            return match ($driver) {
                'file' => new FilesystemAdapter(
                    'nppes',
                    config('nppes.cache.ttl', 3600),
                    storage_path('framework/cache/nppes')
                ),
                'array' => new ArrayAdapter(),
                default => new ArrayAdapter(),
            };
        });

        $this->app->singleton(NpiApiClient::class, function ($app) {
            return new NpiApiClient(
                $app->make('nppes.http-client'),
                $app->make('log')
            );
        });

        $this->app->singleton(NpiCacheService::class, function ($app) {
            return new NpiCacheService(
                $app->make('nppes.cache-adapter'),
                $app->make('log')
            );
        });

        $this->app->singleton(NpiLookupService::class, function ($app) {
            return new NpiLookupService(
                $app->make('nppes.http-client'),
                $app->make('log'),
                $app->make(NpiCacheService::class),
                $app->make(NpiApiClient::class)
            );
        });

        $this->app->bind(NpiLookupInterface::class, NpiLookupService::class);

        $this->app->singleton('nppes', function ($app) {
            return $app->make(NpiLookupService::class);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/nppes.php' => config_path('nppes.php'),
            ], 'nppes-config');

            $this->commands([
                \DolmatovDev\NPPES\Console\LookupNpiCommand::class,
                \DolmatovDev\NPPES\Console\ValidateNpiCommand::class,
                \DolmatovDev\NPPES\Console\VersionCommand::class,
            ]);
        }
    }

    public function provides(): array
    {
        return [
            'nppes',
            'nppes.http-client',
            'nppes.cache-adapter',
            NpiLookupService::class,
            NpiApiClient::class,
            NpiCacheService::class,
            NpiLookupInterface::class,
        ];
    }
}
