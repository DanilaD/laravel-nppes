<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\DTOs;

readonly class NpiTaxonomy
{
    public function __construct(
        public string $code,
        public string $description,
        public bool $isPrimary = false,
        public ?string $state = null,
        public ?string $license = null
    ) {
        $this->validateCode($code);
        if ($state) {
            $this->validateState($state);
        }
    }

    public function getFormattedTaxonomy(): string
    {
        $parts = [$this->code, $this->description];
        
        if ($this->state) {
            $parts[] = "({$this->state})";
        }
        
        if ($this->license) {
            $parts[] = "License: {$this->license}";
        }
        
        return implode(' - ', $parts);
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function hasState(): bool
    {
        return $this->state !== null;
    }

    public function hasLicense(): bool
    {
        return $this->license !== null;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'description' => $this->description,
            'is_primary' => $this->isPrimary,
            'state' => $this->state,
            'license' => $this->license,
        ];
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            code: $data['code'] ?? '',
            description: $data['desc'] ?? '',
            isPrimary: ($data['primary'] ?? false) === true,
            state: $data['state'] ?? null,
            license: $data['license'] ?? null
        );
    }

    private function validateCode(string $code): void
    {
        if (!preg_match('/^\d{10}X$/', $code)) {
            throw new \InvalidArgumentException('Taxonomy code must be 10 digits followed by X');
        }
    }

    private function validateState(string $state): void
    {
        if (!preg_match('/^[A-Z]{2}$/', $state)) {
            throw new \InvalidArgumentException('State must be a 2-letter uppercase code');
        }
    }
}
