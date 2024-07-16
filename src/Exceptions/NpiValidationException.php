<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Exceptions;

class NpiValidationException extends NpiLookupException
{
    public function __construct(
        string $message,
        public readonly array $errors = []
    ) {
        parent::__construct($message);
    }

    public static function invalidNpiNumber(string $npi, array $errors = []): self
    {
        return new self(
            "Invalid NPI number: {$npi}",
            $errors
        );
    }

    public static function invalidSearchParameters(array $errors = []): self
    {
        return new self(
            "Invalid search parameters provided",
            $errors
        );
    }

    public static function missingRequiredField(string $field): self
    {
        return new self(
            "Missing required field: {$field}",
            ['field' => $field, 'type' => 'missing']
        );
    }

    public static function invalidFieldFormat(string $field, string $expectedFormat): self
    {
        return new self(
            "Invalid format for field '{$field}'. Expected: {$expectedFormat}",
            ['field' => $field, 'expected_format' => $expectedFormat, 'type' => 'format']
        );
    }

    public static function fieldValueOutOfRange(string $field, mixed $value, mixed $min, mixed $max): self
    {
        return new self(
            "Field '{$field}' value '{$value}' is out of range. Must be between {$min} and {$max}",
            ['field' => $field, 'value' => $value, 'min' => $min, 'max' => $max, 'type' => 'range']
        );
    }
}
