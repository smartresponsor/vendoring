<?php

declare(strict_types=1);

require_once __DIR__.'/_composer_json.php';

$root = dirname(__DIR__, 2);
$composer = vendoring_load_composer_json($root);
$scripts = vendoring_composer_section($composer, 'scripts');

if (!isset($scripts['test:transaction-doctrine'])) {
    fwrite(STDERR, "Missing composer script test:transaction-doctrine\n");
    exit(1);
}

$entityPath = $root.'/src/Entity/Vendor/VendorTransaction.php';
$interfacePath = $root.'/src/EntityInterface/VendorTransactionInterface.php';

foreach ([$entityPath, $interfacePath] as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, sprintf("Missing required transaction doctrine file: %s\n", $path));
        exit(1);
    }
}

$entitySource = (string) file_get_contents($entityPath);

foreach ([
    '#[ORM\\Entity',
    "#[ORM\\Table(name: 'vendor_transaction')]",
    'implements VendorTransactionInterface',
    "#[ORM\\Column(type: 'decimal', precision: 12, scale: 2)]",
    "#[ORM\\Column(type: 'datetime_immutable')]",
] as $needle) {
    if (!str_contains($entitySource, $needle)) {
        fwrite(STDERR, sprintf("VendorTransaction doctrine contract missing: %s\n", $needle));
        exit(1);
    }
}

echo "Transaction doctrine smoke OK\n";
