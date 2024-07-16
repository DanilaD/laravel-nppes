<?php

return [
    'api' => [
        'base_url' => env('NPPES_API_BASE_URL', 'https://npiregistry.cms.hhs.gov/api/'),
        'timeout' => env('NPPES_API_TIMEOUT', 30),
        'rate_limit' => env('NPPES_RATE_LIMIT', 100),
        'user_agent' => env('NPPES_USER_AGENT', 'DolmatovDev-NPPES-Package/1.0'),
    ],

    'cache' => [
        'driver' => env('NPPES_CACHE_DRIVER', 'array'),
        'prefix' => env('NPPES_CACHE_PREFIX', 'nppes'),
        'ttl' => [
            'lookup' => env('NPPES_CACHE_LOOKUP_TTL', 3600),
            'search' => env('NPPES_CACHE_SEARCH_TTL', 1800),
        ],
    ],

    'logging' => [
        'enabled' => env('NPPES_LOGGING_ENABLED', true),
        'level' => env('NPPES_LOG_LEVEL', 'info'),
        'channel' => env('NPPES_LOG_CHANNEL', 'default'),
    ],

    'validation' => [
        'strict_mode' => env('NPPES_STRICT_MODE', false),
        'checksum_validation' => env('NPPES_CHECKSUM_VALIDATION', true),
        'inactive_npis' => env('NPPES_ALLOW_INACTIVE_NPIS', false),
    ],

    'search' => [
        'default_limit' => env('NPPES_DEFAULT_LIMIT', 10),
        'max_limit' => env('NPPES_MAX_LIMIT', 200),
        'default_skip' => env('NPPES_DEFAULT_SKIP', 0),
    ],

    'error_handling' => [
        'retry_attempts' => env('NPPES_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('NPPES_RETRY_DELAY', 1000),
        'throw_on_error' => env('NPPES_THROW_ON_ERROR', true),
    ],

    'commands' => [
        'enabled' => env('NPPES_COMMANDS_ENABLED', true),
        'prefix' => env('NPPES_COMMAND_PREFIX', 'nppes'),
    ],
];
