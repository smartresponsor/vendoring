<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$services = (string) file_get_contents($root . '/config/component/services.yaml');

$checks = [
    'component/services.yaml excludes src/RepositoryInterface' => str_contains($services, '../../src/RepositoryInterface/'),
    'component/services.yaml excludes src/ServiceInterface' => str_contains($services, '../../src/ServiceInterface/'),
    'component/services.yaml excludes src/DTO' => str_contains($services, '../../src/DTO/'),
    'component/services.yaml aliases VendorApiKeyRepositoryInterface' => str_contains($services, 'App\Vendoring\\RepositoryInterface\\Vendor\\VendorApiKeyRepositoryInterface'),
    'component/services.yaml aliases VendorStatementServiceInterface' => str_contains($services, 'App\Vendoring\\ServiceInterface\\Statement\\VendorStatementServiceInterface'),
    'component/services.yaml aliases VendorStatementMailerServiceInterface' => str_contains($services, 'App\Vendoring\\ServiceInterface\\Statement\\VendorStatementMailerServiceInterface'),
    'component/services.yaml aliases VendorWebhooksConsumerServiceInterface' => str_contains($services, 'App\Vendoring\\ServiceInterface\\WebhooksConsumer\\VendorWebhooksConsumerServiceInterface'),
    'VendorApiKeyRepository exists' => is_file($root . '/src/Repository/Vendor/VendorApiKeyRepository.php'),
];

foreach ($checks as $label => $result) {
    if (true !== $result) {
        fwrite(STDERR, '[FAIL] ' . $label . PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] ' . $label . PHP_EOL);
}
