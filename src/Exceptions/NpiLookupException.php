<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Exceptions;

use Exception;

class NpiLookupException extends Exception
{
    public static function invalidNpiFormat(string $npi): self
    {
        return new self("Invalid NPI format: {$npi}. NPI must be exactly 10 digits.");
    }

    public static function npiNotFound(string $npi): self
    {
        return new self("NPI not found: {$npi}");
    }

    public static function invalidSearchCriteria(string $message): self
    {
        return new self("Invalid search criteria: {$message}");
    }

    public static function rateLimitExceeded(): self
    {
        return new self("API rate limit exceeded. Please try again later.");
    }

    public static function serviceUnavailable(): self
    {
        return new self("NPPES service is currently unavailable.");
    }
}
