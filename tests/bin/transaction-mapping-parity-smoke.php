<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$entity = file_get_contents($root.'/src/Entity/Vendor/VendorTransaction.php');
$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);

$checks = [
    'vendor transaction entity exists' => is_string($entity) && '' !== $entity,
    'entity maps vendor_id' => is_string($entity) && str_contains($entity, "name: 'vendor_id'"),
    'entity maps order_id' => is_string($entity) && str_contains($entity, "name: 'order_id'"),
    'entity maps project_id' => is_string($entity) && str_contains($entity, "name: 'project_id'"),
    'entity maps status column explicitly' => is_string($entity) && str_contains($entity, "name: 'status'"),
    'entity maps created_at explicitly' => is_string($entity) && str_contains($entity, "name: 'created_at'"),
    'composer has transaction mapping script' => isset($composer['scripts']['test:transaction-mapping']),
    'composer has transaction status persistence script' => isset($composer['scripts']['test:transaction-status-persistence']),
];

foreach ($checks as $label => $result) {
    if (true !== $result) {
        fwrite(STDERR, '[FAIL] '.$label.PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] '.$label.PHP_EOL);
}
