<?php

declare(strict_types=1);

$root = realpath(__DIR__ . '/../..');
if (!is_string($root) || '' === $root) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(2);
}

$entityPath = $root . '/src/Entity/Vendor/VendorTransactionEntity.php';
$interfacePath = $root . '/src/EntityInterface/VendorTransactionEntityInterface.php';

foreach ([$entityPath, $interfacePath] as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, sprintf("Missing required doctrine artifact: %s\n", str_replace($root . '/', '', $path)));
        exit(1);
    }
}

$entitySource = (string) file_get_contents($entityPath);
$needles = [
    '#[ORM\\Entity',
    "name: 'vendor_transaction'",
    'implements VendorTransactionEntityInterface',
    "name: 'vendor_id'",
    "name: 'status'",
    "name: 'created_at'",
];

foreach ($needles as $needle) {
    if (!str_contains($entitySource, $needle)) {
        fwrite(STDERR, sprintf("VendorTransactionEntity doctrine mapping missing: %s\n", $needle));
        exit(1);
    }
}

fwrite(STDOUT, "Vendor doctrine mapping smoke passed\n");
