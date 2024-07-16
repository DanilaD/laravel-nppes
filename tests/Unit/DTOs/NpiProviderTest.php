<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs;

use DolmatovDev\NPPES\DTOs\NpiProvider;
use DolmatovDev\NPPES\Tests\TestCase;

class NpiProviderTest extends TestCase
{
    public function test_can_create_npi_provider(): void
    {
        $provider = new NpiProvider(
            npi: "1234567890",
            enumerationType: "INDIVIDUAL",
            status: "ACTIVE",
            credential: "MD",
            firstName: "John",
            lastName: "Doe",
            organizationName: null,
            gender: "M",
            namePrefix: "Dr.",
            soleProprietor: false,
            addresses: [],
            taxonomies: []
        );

        $this->assertEquals("1234567890", $provider->npi);
        $this->assertEquals("INDIVIDUAL", $provider->enumerationType);
        $this->assertEquals("ACTIVE", $provider->status);
    }
}
