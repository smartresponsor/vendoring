<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$services = (string) file_get_contents($root . '/config/vendor_services.yaml');

$checks = [
    'vendor_services.yaml excludes src/RepositoryInterface' => str_contains($services, '../src/RepositoryInterface/'),
    'vendor_services.yaml excludes src/ServiceInterface' => str_contains($services, '../src/ServiceInterface/'),
    'vendor_services.yaml excludes src/DTO' => str_contains($services, '../src/DTO/'),
    'vendor_services.yaml aliases VendorApiKeyRepositoryInterface' => str_contains($services, 'App\\RepositoryInterface\\VendorApiKeyRepositoryInterface'),
    'vendor_services.yaml aliases VendorStatementServiceInterface' => str_contains($services, 'App\\ServiceInterface\\Statement\\VendorStatementServiceInterface'),
    'vendor_services.yaml aliases VendorStatementMailerServiceInterface' => str_contains($services, 'App\\ServiceInterface\\Statement\\VendorStatementMailerServiceInterface'),
    'vendor_services.yaml aliases VendorWebhooksConsumerServiceInterface' => str_contains($services, 'App\\ServiceInterface\\WebhooksConsumer\\VendorWebhooksConsumerServiceInterface'),
    'VendorApiKeyRepository exists' => is_file($root . '/src/Repository/VendorApiKeyRepository.php'),
];

foreach ($checks as $label => $result) {
    if (true !== $result) {
        fwrite(STDERR, '[FAIL] ' . $label . PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] ' . $label . PHP_EOL);
}
