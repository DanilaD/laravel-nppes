# DolmatovDev NPPES Package

A modern PHP 8.1+ Laravel package for interacting with the NPPES (National Provider Identifier) Registry API.

## Features

- 🔍 **NPI Lookup**: Look up healthcare providers by NPI number
- 🔎 **Advanced Search**: Search by name, organization, taxonomy, location
- ✅ **Validation**: NPI format and checksum validation
- 🚀 **Caching**: Built-in caching for improved performance
- 📊 **Rate Limiting**: Automatic API rate limiting
- 🎯 **Laravel Integration**: Service provider, facade, and console commands
- 🧪 **Testing**: Comprehensive test suite

## Requirements

- PHP 8.1+
- Laravel 10.0+ or 11.0+
- Symfony HTTP Client
- PSR Cache and Log interfaces

## Installation

```bash
composer require dolmatovdev/nppes
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=nppes-config
```

This will create `config/nppes.php` with the following options:

- **API Settings**: Base URL, timeout, rate limits
- **Cache Settings**: Driver, TTL, prefix
- **Logging**: Enable/disable, log level
- **Validation**: Strict mode, checksum validation
- **Search**: Default limits and pagination

## Usage

### Basic NPI Lookup

```php
use DolmatovDev\NPPES\Facades\Nppes;

// Look up a provider
$provider = Nppes::lookup('1234567890');

if ($provider) {
    echo "Provider: " . $provider->getFullName();
    echo "Status: " . $provider->status;
    echo "Type: " . ($provider->isIndividual() ? 'Individual' : 'Organization');
}
```

### Search Providers

```php
// Search by name
$results = Nppes::searchByName('John', 'Smith', 'CA');

// Search organization
$results = Nppes::searchOrganization('Mayo Clinic', 'MN');

// Custom search
$results = Nppes::searchByCriteria([
    'first_name' => 'John',
    'last_name' => 'Smith',
    'state' => 'CA',
    'limit' => 20
]);
```

### Validation

```php
// Basic validation
$isValid = Nppes::isValid('1234567890');

// Get validation details
$provider = Nppes::lookup('1234567890');
if ($provider && $provider->isActive()) {
    echo "NPI is valid and active";
}
```

### Console Commands

```bash
# Look up an NPI
php artisan nppes:lookup 1234567890 --verbose

# Validate an NPI
php artisan nppes:validate 1234567890 --checksum --api

# Show package version
php artisan nppes:version
```

## Architecture

### Services

- **NpiLookupService**: Main service for NPI operations
- **NpiApiClient**: Handles HTTP requests to NPPES API
- **NpiCacheService**: Manages caching of results

### DTOs

- **NpiProvider**: Represents a healthcare provider
- **NpiAddress**: Provider address information
- **NpiTaxonomy**: Provider specialty/taxonomy
- **NpiSearchRequest**: Search criteria
- **NpiSearchResponse**: Search results

### Traits

- **HasNpiValidation**: Common validation methods

## Testing

```bash
# Run tests
composer test

# Run with coverage
composer test-coverage
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

For support, please open an issue on GitHub or contact the maintainer.
