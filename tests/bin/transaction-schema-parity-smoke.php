<?php

declare(strict_types=1);

require_once __DIR__.'/_composer_json.php';

$root = dirname(__DIR__, 2);
$entity = (string) file_get_contents($root.'/src/Entity/VendorTransaction.php');
$pg = (string) file_get_contents($root.'/migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql');
$sqlite = (string) file_get_contents($root.'/migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql');
$composer = vendoring_load_composer_json($root);

if (!str_contains($entity, 'idx_vendor_transaction_vendor_created')) {
    fwrite(STDERR, "VendorTransaction entity must declare vendor-created index metadata.\n");
    exit(1);
}

if (!str_contains($entity, 'uniq_vendor_transaction_vendor_order_project')) {
    fwrite(STDERR, "VendorTransaction entity must declare vendor/order/project unique metadata.\n");
    exit(1);
}

foreach ([$pg, $sqlite] as $sql) {
    if (!str_contains($sql, 'uniq_vendor_transaction_vendor_order_project_nonnull') || !str_contains($sql, 'uniq_vendor_transaction_vendor_order_nullproject')) {
        fwrite(STDERR, "Vendor transaction SQL migrations must keep null-aware unique indexes.\n");
        exit(1);
    }
}

if (!vendoring_has_script($composer, 'test:transaction-schema-parity')) {
    fwrite(STDERR, "composer.json must define test:transaction-schema-parity.\n");
    exit(1);
}

echo "transaction schema parity smoke OK\n";
