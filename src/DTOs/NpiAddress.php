<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\DTOs;

readonly class NpiAddress
{
    public function __construct(
        public string $address1,
        public string $city,
        public string $state,
        public string $postalCode,
        public string $addressPurpose = 'PRIMARY',
        public bool $isPrimary = false,
        public ?string $address2 = null,
        public ?string $country = 'US'
    ) {
        $this->validateState($state);
        $this->validatePostalCode($postalCode);
        $this->validateAddressPurpose($addressPurpose);
    }

    public function getFormattedAddress(): string
    {
        $parts = [$this->address1];
        
        if ($this->address2) {
            $parts[] = $this->address2;
        }
        
        $parts[] = "{$this->city}, {$this->state} {$this->postalCode}";
        
        if ($this->country && $this->country !== 'US') {
            $parts[] = $this->country;
        }
        
        return implode("\n", $parts);
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function isDomestic(): bool
    {
        return $this->country === 'US' || $this->country === null;
    }

    public function toArray(): array
    {
        return [
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
            'address_purpose' => $this->addressPurpose,
            'is_primary' => $this->isPrimary,
        ];
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            address1: $data['address_1'] ?? '',
            city: $data['city'] ?? '',
            state: $data['state'] ?? '',
            postalCode: $data['postal_code'] ?? '',
            addressPurpose: $data['address_purpose'] ?? 'PRIMARY',
            isPrimary: ($data['address_purpose'] ?? '') === 'PRIMARY',
            address2: $data['address_2'] ?? null,
            country: $data['country_code'] ?? 'US'
        );
    }

    private function validateState(string $state): void
    {
        if (!preg_match('/^[A-Z]{2}$/', $state)) {
            throw new \InvalidArgumentException('State must be a 2-letter uppercase code');
        }
    }

    private function validatePostalCode(string $postalCode): void
    {
        if (!preg_match('/^\d{5}(-\d{4})?$/', $postalCode)) {
            throw new \InvalidArgumentException('Postal code must be in format 12345 or 12345-6789');
        }
    }

    private function validateAddressPurpose(string $addressPurpose): void
    {
        $validPurposes = ['PRIMARY', 'SECONDARY', 'LOCATION'];
        if (!in_array($addressPurpose, $validPurposes, true)) {
            throw new \InvalidArgumentException(
                'Address purpose must be one of: ' . implode(', ', $validPurposes)
            );
        }
    }
}
