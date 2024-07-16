<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\DTOs;

readonly class NpiSearchResponse
{
    public function __construct(
        public array $results,
        public int $resultCount,
        public int $skip,
        public int $limit,
        public bool $hasMore = false
    ) {
        $this->validateResultCount($resultCount);
        $this->validateSkip($skip);
        $this->validateLimit($limit);
    }

    public function first(): ?NpiProvider
    {
        return $this->results[0] ?? null;
    }

    public function all(): array
    {
        return $this->results;
    }

    public function isEmpty(): bool
    {
        return empty($this->results);
    }

    public function isNotEmpty(): bool
    {
        return !empty($this->results);
    }

    public function count(): int
    {
        return count($this->results);
    }

    public function toArray(): array
    {
        return [
            'results' => array_map(fn($provider) => $provider->toArray(), $this->results),
            'result_count' => $this->resultCount,
            'skip' => $this->skip,
            'limit' => $this->limit,
            'has_more' => $this->hasMore,
        ];
    }

    public static function fromApiResponse(array $data): self
    {
        $results = array_map(
            fn($providerData) => NpiProvider::fromApiResponse($providerData),
            $data['results'] ?? []
        );

        return new self(
            results: $results,
            resultCount: $data['result_count'] ?? 0,
            skip: $data['skip'] ?? 0,
            limit: $data['limit'] ?? 10,
            hasMore: ($data['result_count'] ?? 0) > (($data['skip'] ?? 0) + ($data['limit'] ?? 10))
        );
    }

    public static function empty(): self
    {
        return new self(
            results: [],
            resultCount: 0,
            skip: 0,
            limit: 10,
            hasMore: false
        );
    }

    private function validateResultCount(int $resultCount): void
    {
        if ($resultCount < 0) {
            throw new \InvalidArgumentException('Result count cannot be negative');
        }
    }

    private function validateSkip(int $skip): void
    {
        if ($skip < 0) {
            throw new \InvalidArgumentException('Skip value cannot be negative');
        }
    }

    private function validateLimit(int $limit): void
    {
        if ($limit < 1 || $limit > 200) {
            throw new \InvalidArgumentException('Limit must be between 1 and 200');
        }
    }
}
