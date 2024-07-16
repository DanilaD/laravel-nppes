<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Contracts;

use DolmatovDev\NPPES\DTOs\NpiProvider;
use DolmatovDev\NPPES\DTOs\NpiSearchRequest;
use DolmatovDev\NPPES\DTOs\NpiSearchResponse;
use DolmatovDev\NPPES\Exceptions\NpiLookupException;

interface NpiLookupInterface
{
    public function lookupByNpi(string $npi): ?NpiProvider;

    public function search(NpiSearchRequest $request): NpiSearchResponse;

    public function searchByCriteria(array $criteria): NpiSearchResponse;

    public function validateNpi(string $npi): bool;

    public function getProviderTaxonomies(string $npi): array;

    public function getProviderAddresses(string $npi): array;

    public function isAvailable(): bool;

    public function getRateLimitInfo(): array;
}
