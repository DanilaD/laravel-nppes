<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\DTOs;

readonly class NpiProvider
{
    public function __construct(
        public string $npi,
        public string $enumerationType,
        public string $status,
        public ?string $credential = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $organizationName = null,
        public ?string $gender = null,
        public ?string $namePrefix = null,
        public ?bool $soleProprietor = null,
        public array $addresses = [],
        public array $taxonomies = []
    ) {
    }

    public function getFullName(): string
    {
        if ($this->isOrganization()) {
            return $this->organizationName ?? 'Unknown Organization';
        }

        $parts = array_filter([
            $this->namePrefix,
            $this->firstName,
            $this->lastName,
            $this->credential
        ]);

        return implode(' ', $parts);
    }

    public function isIndividual(): bool
    {
        return $this->enumerationType === 'INDIVIDUAL';
    }

    public function isOrganization(): bool
    {
        return $this->enumerationType === 'ORGANIZATION';
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    public function toArray(): array
    {
        return [
            'npi' => $this->npi,
            'enumeration_type' => $this->enumerationType,
            'status' => $this->status,
            'credential' => $this->credential,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'organization_name' => $this->organizationName,
            'gender' => $this->gender,
            'name_prefix' => $this->namePrefix,
            'sole_proprietor' => $this->soleProprietor,
            'addresses' => $this->addresses,
            'taxonomies' => $this->taxonomies,
        ];
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            npi: $data['number'] ?? '',
            enumerationType: $data['enumeration_type'] ?? '',
            status: $data['status'] ?? '',
            credential: $data['credential'] ?? null,
            firstName: $data['basic']['first_name'] ?? null,
            lastName: $data['basic']['last_name'] ?? null,
            organizationName: $data['basic']['organization_name'] ?? null,
            gender: $data['basic']['gender'] ?? null,
            namePrefix: $data['basic']['name_prefix'] ?? null,
            soleProprietor: $data['basic']['sole_proprietor'] ?? null,
            addresses: $data['addresses'] ?? [],
            taxonomies: $data['taxonomies'] ?? []
        );
    }
}
