<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\DTOs;

readonly class NpiSearchRequest
{
    public function __construct(
        public ?string $number = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $organizationName = null,
        public ?string $state = null,
        public ?string $city = null,
        public ?string $zip = null,
        public ?string $enumerationType = null,
        public ?string $taxonomyDescription = null,
        public ?string $addressPurpose = null,
        public int $limit = 10,
        public int $skip = 0
    ) {
        $this->validateLimit($limit);
        if ($state) {
            $this->validateState($state);
        }
        if ($zip) {
            $this->validateZip($zip);
        }
        if ($enumerationType) {
            $this->validateEnumerationType($enumerationType);
        }
    }

    public function isNumberSearch(): bool
    {
        return $this->number !== null;
    }

    public function isNameSearch(): bool
    {
        return $this->firstName !== null || $this->lastName !== null;
    }

    public function isOrganizationSearch(): bool
    {
        return $this->organizationName !== null;
    }

    public function toArray(): array
    {
        return array_filter([
            'number' => $this->number,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'organization_name' => $this->organizationName,
            'state' => $this->state,
            'city' => $this->city,
            'zip' => $this->zip,
            'enumeration_type' => $this->enumerationType,
            'taxonomy_description' => $this->taxonomyDescription,
            'address_purpose' => $this->addressPurpose,
            'limit' => $this->limit,
            'skip' => $this->skip,
        ]);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            number: $data['number'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            organizationName: $data['organization_name'] ?? null,
            state: $data['state'] ?? null,
            city: $data['city'] ?? null,
            zip: $data['zip'] ?? null,
            enumerationType: $data['enumeration_type'] ?? null,
            taxonomyDescription: $data['taxonomy_description'] ?? null,
            addressPurpose: $data['address_purpose'] ?? null,
            limit: $data['limit'] ?? 10,
            skip: $data['skip'] ?? 0
        );
    }

    private function validateLimit(int $limit): void
    {
        if ($limit < 1 || $limit > 200) {
            throw new \InvalidArgumentException('Limit must be between 1 and 200');
        }
    }

    private function validateState(string $state): void
    {
        if (!preg_match('/^[A-Z]{2}$/', $state)) {
            throw new \InvalidArgumentException('State must be a 2-letter uppercase code');
        }
    }

    private function validateZip(string $zip): void
    {
        if (!preg_match('/^\d{5}(-\d{4})?$/', $zip)) {
            throw new \InvalidArgumentException('ZIP code must be in format 12345 or 12345-6789');
        }
    }

    private function validateEnumerationType(string $enumerationType): void
    {
        $validTypes = ['INDIVIDUAL', 'ORGANIZATION'];
        if (!in_array($enumerationType, $validTypes, true)) {
            throw new \InvalidArgumentException(
                'Enumeration type must be one of: ' . implode(', ', $validTypes)
            );
        }
    }
}
