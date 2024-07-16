<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Console;

use DolmatovDev\NPPES\Contracts\NpiLookupInterface;
use DolmatovDev\NPPES\DTOs\NpiProvider;
use Illuminate\Console\Command;

class LookupNpiCommand extends Command
{
    protected $signature = 'nppes:lookup {npi : The NPI number to look up} 
                            {--format=table : Output format (table, json, csv)}
                            {--verbose : Show detailed information}';

    protected $description = 'Look up a provider by NPI number';

    public function __construct(
        private readonly NpiLookupInterface $npiService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $npi = $this->argument('npi');
        $format = $this->option('format');
        $verbose = $this->option('verbose');

        $this->info("Looking up NPI: {$npi}");

        try {
            $provider = $this->npiService->lookupByNpi($npi);

            if ($provider === null) {
                $this->error("NPI {$npi} not found");
                return self::FAILURE;
            }

            $this->displayProvider($provider, $format, $verbose);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error looking up NPI: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function displayProvider(NpiProvider $provider, string $format, bool $verbose): void
    {
        switch ($format) {
            case 'json':
                $this->displayJson($provider, $verbose);
                break;
            case 'csv':
                $this->displayCsv($provider, $verbose);
                break;
            default:
                $this->displayTable($provider, $verbose);
                break;
        }
    }

    private function displayTable(NpiProvider $provider, bool $verbose): void
    {
        $this->table(
            ['Field', 'Value'],
            [
                ['NPI', $provider->npi],
                ['Name', $provider->getFullName()],
                ['Type', $provider->enumerationType],
                ['Status', $provider->status],
                ['Credential', $provider->credential ?? 'N/A'],
            ]
        );

        if ($verbose) {
            $this->displayAddresses($provider);
            $this->displayTaxonomies($provider);
        }
    }

    private function displayJson(NpiProvider $provider, bool $verbose): void
    {
        $data = $provider->toArray();
        
        if (!$verbose) {
            unset($data['addresses'], $data['taxonomies']);
        }
        
        $this->line(json_encode($data, JSON_PRETTY_PRINT));
    }

    private function displayCsv(NpiProvider $provider, bool $verbose): void
    {
        $this->line("NPI,Name,Type,Status,Credential");
        $this->line(sprintf(
            '"%s","%s","%s","%s","%s"',
            $provider->npi,
            $provider->getFullName(),
            $provider->enumerationType,
            $provider->status,
            $provider->credential ?? ''
        ));
    }

    private function displayAddresses(NpiProvider $provider): void
    {
        if (empty($provider->addresses)) {
            $this->line("\n<comment>No addresses found</comment>");
            return;
        }

        $this->line("\n<info>Addresses:</info>");
        
        foreach ($provider->addresses as $index => $address) {
            $this->line("  Address " . ($index + 1) . ":");
            $this->line("    " . $address['address1']);
            if ($address['address2']) {
                $this->line("    " . $address['address2']);
            }
            $this->line("    {$address['city']}, {$address['state']} {$address['postal_code']}");
            $this->line("");
        }
    }

    private function displayTaxonomies(NpiProvider $provider): void
    {
        if (empty($provider->taxonomies)) {
            $this->line("\n<comment>No taxonomies found</comment>");
            return;
        }

        $this->line("\n<info>Taxonomies:</info>");
        
        foreach ($provider->taxonomies as $index => $taxonomy) {
            $this->line("  " . ($index + 1) . ". {$taxonomy['code']} - {$taxonomy['description']}");
            if ($taxonomy['is_primary']) {
                $this->line("     <comment>(Primary)</comment>");
            }
        }
    }
}
