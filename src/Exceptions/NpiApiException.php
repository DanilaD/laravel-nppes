<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Exceptions;

class NpiApiException extends NpiLookupException
{
    public function __construct(
        string $message,
        public readonly array $responseData = [],
        public readonly int $httpStatusCode = 0
    ) {
        parent::__construct($message);
    }

    public static function httpError(int $statusCode, string $message): self
    {
        return new self(
            "HTTP Error {$statusCode}: {$message}",
            [],
            $statusCode
        );
    }

    public static function apiError(string $message, array $responseData = []): self
    {
        return new self(
            "API Error: {$message}",
            $responseData
        );
    }

    public static function networkError(string $message): self
    {
        return new self("Network Error: {$message}");
    }

    public static function timeout(int $timeout): self
    {
        return new self("Request timed out after {$timeout} seconds");
    }
}
