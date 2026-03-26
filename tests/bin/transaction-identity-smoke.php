<?php

declare(strict_types=1);

require_once __DIR__.'/_composer_json.php';

$root = dirname(__DIR__, 2);
$entity = (string) file_get_contents($root.'/src/Entity/Vendor/VendorTransaction.php');
$event = (string) file_get_contents($root.'/src/Event/VendorTransactionEvent.php');
$repo = (string) file_get_contents($root.'/src/Repository/VendorTransactionRepository.php');
$composer = vendoring_load_composer_json($root);

foreach (['vendorId', 'orderId', 'projectId', 'amount', 'status'] as $needle) {
    if (!str_contains($entity, $needle)) {
        fwrite(STDERR, "VendorTransaction entity missing identity member: {$needle}\n");
        exit(1);
    }
}
if (!str_contains($event, 'VendorTransaction')) {
    fwrite(STDERR, "VendorTransactionEvent must reference VendorTransaction\n");
    exit(1);
}
if (!str_contains($repo, 'existsForVendorOrderProject')) {
    fwrite(STDERR, "VendorTransactionRepository must define duplicate-check lookup\n");
    exit(1);
}
if (!vendoring_has_script($composer, 'test:transaction-identity')) {
    fwrite(STDERR, "composer.json must define test:transaction-identity\n");
    exit(1);
}

echo "transaction identity smoke passed\n";
