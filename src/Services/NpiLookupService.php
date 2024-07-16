<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Services;

use DolmatovDev\NPPES\Contracts\NpiLookupInterface;
use DolmatovDev\NPPES\DTOs\NpiProvider;
use DolmatovDev\NPPES\DTOs\NpiSearchRequest;
use DolmatovDev\NPPES\DTOs\NpiSearchResponse;
use DolmatovDev\NPPES\Exceptions\NpiLookupException;
use DolmatovDev\NPPES\Exceptions\NpiValidationException;
use DolmatovDev\NPPES\Traits\HasNpiValidation;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NpiLookupService implements NpiLookupInterface
{
    use HasNpiValidation;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly NpiCacheService $cacheService,
        private readonly NpiApiClient $apiClient
    ) {
    }

    public function lookupByNpi(string $npi): ?NpiProvider
    {
        if (!$this->isValidNpiFormat($npi)) {
            throw NpiValidationException::invalidNpiNumber($npi, ['format' => 'invalid']);
        }

        $cacheKey = "npi_lookup_{$npi}";
        $cached = $this->cacheService->get($cacheKey);
        
        if ($cached !== null) {
            $this->logger->info("NPI lookup from cache", ['npi' => $npi]);
            return NpiProvider::fromApiResponse($cached);
        }

        try {
            $response = $this->apiClient->lookupByNumber($npi);
            
            if (empty($response['results'])) {
                $this->logger->info("NPI not found", ['npi' => $npi]);
                return null;
            }

            $providerData = $response['results'][0];
            
            $this->cacheService->put($cacheKey, $providerData, 3600);
            
            $this->logger->info("NPI lookup successful", ['npi' => $npi]);
            
            return NpiProvider::fromApiResponse($providerData);
            
        } catch (\Exception $e) {
            $this->logger->error("NPI lookup failed", [
                'npi' => $npi,
                'error' => $e->getMessage()
            ]);
            
            throw new NpiLookupException("Failed to lookup NPI {$npi}: " . $e->getMessage());
        }
    }

    public function search(NpiSearchRequest $request): NpiSearchResponse
    {
        return $this->searchByCriteria($request->toArray());
    }

    public function searchByCriteria(array $criteria): NpiSearchResponse
    {
        $errors = $this->validateSearchCriteria($criteria);
        if (!empty($errors)) {
            throw NpiValidationException::invalidSearchParameters($errors);
        }

        $cacheKey = "npi_search_" . md5(serialize($criteria));
        
        $cached = $this->cacheService->get($cacheKey);
        if ($cached !== null) {
            $this->logger->info("NPI search from cache", ['criteria' => $criteria]);
            return NpiSearchResponse::fromApiResponse($cached);
        }

        try {
            $response = $this->apiClient->search($criteria);
            
            $this->cacheService->put($cacheKey, $response, 1800);
            
            $this->logger->info("NPI search successful", [
                'criteria' => $criteria,
                'result_count' => $response['result_count'] ?? 0
            ]);
            
            return NpiSearchResponse::fromApiResponse($response);
            
        } catch (\Exception $e) {
            $this->logger->error("NPI search failed", [
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            
            throw new NpiLookupException("Failed to search NPIs: " . $e->getMessage());
        }
    }

    public function validateNpi(string $npi): bool
    {
        if (!$this->isValidNpiFormat($npi)) {
            return false;
        }

        try {
            $provider = $this->lookupByNpi($npi);
            return $provider !== null && $provider->isActive();
        } catch (\Exception $e) {
            $this->logger->warning("NPI validation failed", [
                'npi' => $npi,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getProviderTaxonomies(string $npi): array
    {
        $provider = $this->lookupByNpi($npi);
        
        if ($provider === null) {
            throw NpiLookupException::npiNotFound($npi);
        }
        
        return $provider->taxonomies;
    }

    public function getProviderAddresses(string $npi): array
    {
        $provider = $this->lookupByNpi($npi);
        
        if ($provider === null) {
            throw NpiLookupException::npiNotFound($npi);
        }
        
        return $provider->addresses;
    }

    public function isAvailable(): bool
    {
        return $this->apiClient->ping();
    }

    public function getRateLimitInfo(): array
    {
        return $this->apiClient->getCurrentUsage();
    }

    private function validateSearchCriteria(array $criteria): array
    {
        $errors = [];
        
        $searchParams = ['number', 'first_name', 'last_name', 'organization_name'];
        $hasSearchParam = false;
        
        foreach ($searchParams as $param) {
            if (!empty($criteria[$param])) {
                $hasSearchParam = true;
                break;
            }
        }
        
        if (!$hasSearchParam) {
            $errors[] = 'At least one search parameter is required';
        }
        
        if (!empty($criteria['state']) && !$this->isValidStateCode($criteria['state'])) {
            $errors[] = 'Invalid state code';
        }
        
        if (!empty($criteria['zip']) && !$this->isValidZipCode($criteria['zip'])) {
            $errors[] = 'Invalid ZIP code format';
        }
        
        return $errors;
    }
}
