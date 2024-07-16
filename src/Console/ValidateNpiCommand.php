<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Console;

use DolmatovDev\NPPES\Contracts\NpiLookupInterface;
use DolmatovDev\NPPES\Traits\HasNpiValidation;
use Illuminate\Console\Command;

class ValidateNpiCommand extends Command
{
    use HasNpiValidation;

    protected $signature = 'nppes:validate {npi : The NPI number to validate}
                            {--checksum : Validate checksum using Luhn algorithm}
                            {--api : Validate against NPPES API}';

    protected $description = 'Validate an NPI number';

    public function __construct(
        private readonly NpiLookupInterface $npiService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $npi = $this->argument('npi');
        $validateChecksum = $this->option('checksum');
        $validateApi = $this->option('api');

        $this->info("Validating NPI: {$npi}");

        $formatValid = $this->isValidFormat($npi);
        $this->line("Format validation: " . ($formatValid ? 'PASS' : 'FAIL'));

        if (!$formatValid) {
            $this->error("NPI format is invalid. Must be exactly 10 digits.");
            return self::FAILURE;
        }

        if ($validateChecksum) {
            $checksumValid = $this->isValidChecksum($npi);
            $this->line("Checksum validation: " . ($checksumValid ? 'PASS' : 'FAIL'));
            
            if (!$checksumValid) {
                $this->error("NPI checksum is invalid.");
                return self::FAILURE;
            }
        }

        if ($validateApi) {
            $apiValid = $this->isValidApi($npi);
            $this->line("API validation: " . ($apiValid ? 'PASS' : 'FAIL'));
            
            if (!$apiValid) {
                $this->error("NPI not found in NPPES registry or is inactive.");
                return self::FAILURE;
            }
        }

        $this->info("NPI validation completed successfully!");
        return self::SUCCESS;
    }

    private function isValidFormat(string $npi): bool
    {
        return $this->isValidNpiFormat($npi);
    }

    private function isValidChecksum(string $npi): bool
    {
        return $this->isValidNpiChecksum($npi);
    }

    private function isValidApi(string $npi): bool
    {
        try {
            return $this->npiService->validateNpi($npi);
        } catch (\Exception $e) {
            $this->error("API validation error: " . $e->getMessage());
            return false;
        }
    }
}
