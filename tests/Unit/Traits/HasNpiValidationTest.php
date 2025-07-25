<?php

declare(strict_types=1);

namespace Tests nit\Traits;

use DolmatovDev\NPPES\Traits\HasNpiValidation;
use Tests\TestCase;

class HasNpiValidationTest extends TestCase
{
    use HasNpiValidation;

    public function test_valid_npi_format(): void
    {
        $this->assertTrue($this->isValidNpiFormat("1234567890"));
        $this->assertTrue($this->isValidNpiFormat("1987654321"));
    }

    public function test_invalid_npi_format(): void
    {
        $this->assertFalse($this->isValidNpiFormat("123456789")); // Too short
        $this->assertFalse($this->isValidNpiFormat("12345678901")); // Too long
        $this->assertFalse($this->isValidNpiFormat("abc123def")); // Mixed
    }
}
