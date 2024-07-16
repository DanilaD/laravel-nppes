<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Facades;

use DolmatovDev\NPPES\Contracts\NpiLookupInterface;
use DolmatovDev\NPPES\DTOs\NpiProvider;
use DolmatovDev\NPPES\DTOs\NpiSearchRequest;
use DolmatovDev\NPPES\DTOs\NpiSearchResponse;
use Illuminate\Support\Facades\Facade;

class Nppes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NpiLookupInterface::class;
    }

    public static function lookup(string $npi): ?NpiProvider
    {
        return static::lookupByNpi($npi);
    }

    public static function searchByName(string $firstName, ?string $lastName = null, ?string $state = null): NpiSearchResponse
    {
        $criteria = ['first_name' => $firstName];
        
        if ($lastName) {
            $criteria['last_name'] = $lastName;
        }
        
        if ($state) {
            $criteria['state'] = $state;
        }
        
        return static::searchByCriteria($criteria);
    }

    public static function searchOrganization(string $organizationName, ?string $state = null): NpiSearchResponse
    {
        $criteria = ['organization_name' => $organizationName];
        
        if ($state) {
            $criteria['state'] = $state;
        }
        
        return static::searchByCriteria($criteria);
    }

    public static function searchByTaxonomy(string $taxonomyDescription, ?string $state = null): NpiSearchResponse
    {
        $criteria = ['taxonomy_description' => $taxonomyDescription];
        
        if ($state) {
            $criteria['state'] = $state;
        }
        
        return static::searchByCriteria($criteria);
    }

    public static function searchByLocation(string $city, string $state, ?string $zip = null): NpiSearchResponse
    {
        $criteria = [
            'city' => $city,
            'state' => $state,
        ];
        
        if ($zip) {
            $criteria['zip'] = $zip;
        }
        
        return static::searchByCriteria($criteria);
    }

    public static function getProviderArray(string $npi): ?array
    {
        $provider = static::lookupByNpi($npi);
        
        return $provider?->toArray();
    }

    public static function isValid(string $npi): bool
    {
        return static::validateNpi($npi);
    }

    public static function getPrimaryAddress(string $npi): ?array
    {
        $addresses = static::getProviderAddresses($npi);
        
        foreach ($addresses as $address) {
            if ($address['is_primary']) {
                return $address;
            }
        }
        
        return $addresses[0] ?? null;
    }

    public static function getPrimaryTaxonomy(string $npi): ?array
    {
        $taxonomies = static::getProviderTaxonomies($npi);
        
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy['is_primary']) {
                return $taxonomy;
            }
        }
        
        return $taxonomies[0] ?? null;
    }
}
