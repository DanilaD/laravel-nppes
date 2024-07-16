<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use DolmatovDev\NPPES\NppesServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            NppesServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'NPPES' => \DolmatovDev\NPPES\Facades\Nppes::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('nppes.api.timeout', 5);
        $app['config']->set('nppes.cache.driver', 'array');
        $app['config']->set('nppes.logging.enabled', false);
    }

    protected function createMockHttpClient(): HttpClientInterface
    {
        return $this->createMock(HttpClientInterface::class);
    }

    protected function createMockCacheAdapter(): CacheItemPoolInterface
    {
        return $this->createMock(CacheItemPoolInterface::class);
    }

    protected function createMockLogger(): LoggerInterface
    {
        return $this->createMock(LoggerInterface::class);
    }
}
