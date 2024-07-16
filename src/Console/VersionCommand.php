<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Console;

use Illuminate\Console\Command;

class VersionCommand extends Command
{
    protected $signature = 'nppes:version';
    protected $description = 'Show the current NPPES package version';

    public function handle(): int
    {
        $version = '1.0.0';
        $this->info("DolmatovDev NPPES Package version: {$version}");
        $this->line("Package: dolmatovdev/nppes");
        $this->line("Author: Danila Dolmatov");
        $this->line("License: MIT");
        
        return self::SUCCESS;
    }
}
