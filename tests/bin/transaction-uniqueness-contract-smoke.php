<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$entity = (string) file_get_contents($root.'/src/Entity/Vendor/VendorTransaction.php');
$pg = (string) file_get_contents($root.'/migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql');
$sqlite = (string) file_get_contents($root.'/migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql');
$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$scripts = $composer['scripts'] ?? [];

$checks = [
    [!str_contains($entity, 'uniqueConstraints'), 'VendorTransaction entity must not declare misleading full uniqueConstraints metadata'],
    [str_contains($pg, 'uniq_vendor_transaction_vendor_order_project_nonnull'), 'PostgreSQL migration must define non-null project unique index'],
    [str_contains($pg, 'uniq_vendor_transaction_vendor_order_nullproject'), 'PostgreSQL migration must define null-project unique index'],
    [str_contains($sqlite, 'uniq_vendor_transaction_vendor_order_project_nonnull'), 'SQLite migration must define non-null project unique index'],
    [str_contains($sqlite, 'uniq_vendor_transaction_vendor_order_nullproject'), 'SQLite migration must define null-project unique index'],
    [array_key_exists('test:transaction-uniqueness-contract', $scripts), 'composer.json must define test:transaction-uniqueness-contract'],
];

foreach ($checks as [$ok, $message]) {
    if (true !== $ok) {
        fwrite(STDERR, $message.PHP_EOL);
        exit(1);
    }
}

echo 'transaction uniqueness contract smoke OK'.PHP_EOL;
