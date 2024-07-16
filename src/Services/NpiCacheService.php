<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Services;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class NpiCacheService
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly LoggerInterface $logger
    ) {
    }

    public function get(string $key): mixed
    {
        try {
            $item = $this->cache->getItem($key);
            
            if ($item->isHit()) {
                $this->logger->debug("Cache hit for key: {$key}");
                return $item->get();
            }
            
            $this->logger->debug("Cache miss for key: {$key}");
            return null;
        } catch (\Exception $e) {
            $this->logger->error("Cache get error for key {$key}: " . $e->getMessage());
            return null;
        }
    }

    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        try {
            $item = $this->cache->getItem($key);
            $item->set($value);
            $item->expiresAfter($ttl);
            
            $result = $this->cache->save($item);
            
            if ($result) {
                $this->logger->debug("Cached item with key: {$key}, TTL: {$ttl}");
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Cache put error for key {$key}: " . $e->getMessage());
            return false;
        }
    }

    public function forget(string $key): bool
    {
        try {
            $result = $this->cache->deleteItem($key);
            
            if ($result) {
                $this->logger->debug("Removed cache item with key: {$key}");
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Cache delete error for key {$key}: " . $e->getMessage());
            return false;
        }
    }

    public function clear(): bool
    {
        try {
            $result = $this->cache->clear();
            
            if ($result) {
                $this->logger->info("Cache cleared successfully");
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Cache clear error: " . $e->getMessage());
            return false;
        }
    }
}
