<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2);
$entity = (string) file_get_contents($root . '/src/Entity/VendorTransactionEntity.php');
$pg = (string) file_get_contents($root . '/migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql');
$sqlite = (string) file_get_contents($root . '/migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql');
$composer = vendoring_load_composer_json($root);
$scripts = vendoring_composer_section($composer, 'scripts');

$checks = [
    [!str_contains($entity, 'uniqueConstraints'), 'VendorTransactionEntity entity must not declare misleading full uniqueConstraints metadata'],
    [str_contains($pg, 'uniq_vendor_transaction_vendor_order_project_nonnull'), 'PostgreSQL migration must define non-null project unique index'],
    [str_contains($pg, 'uniq_vendor_transaction_vendor_order_nullproject'), 'PostgreSQL migration must define null-project unique index'],
    [str_contains($sqlite, 'uniq_vendor_transaction_vendor_order_project_nonnull'), 'SQLite migration must define non-null project unique index'],
    [str_contains($sqlite, 'uniq_vendor_transaction_vendor_order_nullproject'), 'SQLite migration must define null-project unique index'],
    [array_key_exists('test:transaction-uniqueness-contract', $scripts), 'composer.json must define test:transaction-uniqueness-contract'],
];

foreach ($checks as [$ok, $message]) {
    if (true !== $ok) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

echo 'transaction uniqueness contract smoke OK' . PHP_EOL;
