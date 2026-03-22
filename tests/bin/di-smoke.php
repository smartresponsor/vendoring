<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$services = (string) file_get_contents($root.'/config/services.yaml');

$checks = [
    'services.yaml excludes src/RepositoryInterface' => str_contains($services, '../src/RepositoryInterface/'),
    'services.yaml excludes src/ServiceInterface' => str_contains($services, '../src/ServiceInterface/'),
    'services.yaml excludes src/DTO' => str_contains($services, '../src/DTO/'),
    'services.yaml aliases VendorApiKeyRepositoryInterface' => str_contains($services, 'App\\RepositoryInterface\\VendorApiKeyRepositoryInterface'),
    'services.yaml aliases VendorStatementServiceInterface' => str_contains($services, 'App\\ServiceInterface\\Statement\\VendorStatementServiceInterface'),
    'services.yaml aliases StatementMailerServiceInterface' => str_contains($services, 'App\\ServiceInterface\\Statement\\StatementMailerServiceInterface'),
    'services.yaml aliases WebhooksConsumerInterface' => str_contains($services, 'App\\ServiceInterface\\WebhooksConsumer\\WebhooksConsumerInterface'),
    'VendorApiKeyRepository exists' => is_file($root.'/src/Repository/VendorApiKeyRepository.php'),
];

foreach ($checks as $label => $result) {
    if (true !== $result) {
        fwrite(STDERR, '[FAIL] '.$label.PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] '.$label.PHP_EOL);
}
