<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2);
$entity = (string) file_get_contents($root . '/src/Entity/VendorTransaction.php');
$migration = (string) file_get_contents($root . '/migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql');
$composer = vendoring_load_composer_json($root);

foreach (['vendor_id', 'order_id', 'project_id', 'amount', 'status', 'created_at'] as $column) {
    if (!str_contains($migration, $column)) {
        fwrite(STDERR, "Migration missing transaction column: {$column}\n");
        exit(1);
    }
}
if (!str_contains($entity, 'VendorTransaction')) {
    fwrite(STDERR, "Entity mapping parity smoke failed\n");
    exit(1);
}
if (!vendoring_has_script($composer, 'test:transaction-mapping')) {
    fwrite(STDERR, "composer.json must define test:transaction-mapping\n");
    exit(1);
}
if (!vendoring_has_script($composer, 'test:transaction-schema-parity')) {
    fwrite(STDERR, "composer.json must define test:transaction-schema-parity\n");
    exit(1);
}

echo "transaction mapping parity smoke passed\n";
