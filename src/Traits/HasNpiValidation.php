<?php

declare(strict_types=1);

namespace DolmatovDev\NPPES\Traits;

trait HasNpiValidation
{
    public function isValidNpiFormat(string $npi): bool
    {
        return preg_match('/^\d{10}$/', $npi) === 1;
    }

    public function isValidNpiChecksum(string $npi): bool
    {
        if (!$this->isValidNpiFormat($npi)) {
            return false;
        }

        return $this->luhnCheck($npi);
    }

    private function luhnCheck(string $number): bool
    {
        $sum = 0;
        $length = strlen($number);
        
        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];
            
            if (($length - $i) % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
        }
        
        return $sum % 10 === 0;
    }

    public function isValidStateCode(string $state): bool
    {
        $validStates = [
            'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA',
            'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
            'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
            'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
            'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY',
            'DC', 'AS', 'GU', 'MP', 'PR', 'VI'
        ];
        
        return in_array(strtoupper($state), $validStates, true);
    }

    public function isValidZipCode(string $zip): bool
    {
        return preg_match('/^\d{5}(-\d{4})?$/', $zip) === 1;
    }

    public function isValidTaxonomyCode(string $taxonomy): bool
    {
        return preg_match('/^\d{10}X$/', $taxonomy) === 1;
    }
}
