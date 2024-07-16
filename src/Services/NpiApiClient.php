<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Services;

use DolmatovDev\NPPES\Exceptions\NpiApiException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

class NpiApiClient
{
    private const API_BASE_URL = 'https://npiregistry.cms.hhs.gov/api/';
    private const RATE_LIMIT_REQUESTS = 100;
    private const RATE_LIMIT_WINDOW = 60;

    private RateLimiterFactory $rateLimiter;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger
    ) {
        $this->initializeRateLimiter();
    }

    public function lookupByNumber(string $npi): array
    {
        $this->checkRateLimit();
        
        $url = self::API_BASE_URL . "?number={$npi}&version=2.1";
        
        try {
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();
            
            $this->logger->info("NPI lookup successful", ['npi' => $npi]);
            
            return $data;
        } catch (\Exception $e) {
            $this->logger->error("NPI lookup failed", [
                'npi' => $npi,
                'error' => $e->getMessage()
            ]);
            
            throw NpiApiException::networkError($e->getMessage());
        }
    }

    public function search(array $criteria): array
    {
        $this->checkRateLimit();
        
        $params = http_build_query($criteria);
        $url = self::API_BASE_URL . "?{$params}&version=2.1";
        
        try {
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();
            
            $this->logger->info("NPI search successful", ['criteria' => $criteria]);
            
            return $data;
        } catch (\Exception $e) {
            $this->logger->error("NPI search failed", [
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            
            throw NpiApiException::networkError($e->getMessage());
        }
    }

    public function ping(): bool
    {
        try {
            $response = $this->httpClient->request('GET', self::API_BASE_URL . '?limit=1');
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            $this->logger->error("API ping failed: " . $e->getMessage());
            return false;
        }
    }

    public function getCurrentUsage(): array
    {
        $limiter = $this->rateLimiter->create('nppes_api');
        $tokens = $limiter->consume(0);
        
        return [
            'remaining' => $tokens->getRemainingTokens(),
            'limit' => self::RATE_LIMIT_REQUESTS,
            'reset_time' => $tokens->getRetryAfter()->getTimestamp(),
        ];
    }

    public function getRemainingRequests(): int
    {
        $usage = $this->getCurrentUsage();
        return $usage['remaining'];
    }

    public function getResetTime(): int
    {
        $usage = $this->getCurrentUsage();
        return $usage['reset_time'];
    }

    private function checkRateLimit(): void
    {
        $limiter = $this->rateLimiter->create('nppes_api');
        
        try {
            $limiter->consume(1);
        } catch (\Exception $e) {
            $this->logger->warning("Rate limit exceeded");
            throw NpiApiException::rateLimitExceeded();
        }
    }

    private function initializeRateLimiter(): void
    {
        $storage = new InMemoryStorage();
        $this->rateLimiter = new RateLimiterFactory([
            'id' => 'nppes_api',
            'policy' => 'sliding_window',
            'limit' => self::RATE_LIMIT_REQUESTS,
            'interval' => self::RATE_LIMIT_WINDOW . ' seconds',
        ], $storage);
    }
}
