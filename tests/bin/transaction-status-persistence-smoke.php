<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$statusCatalog = file_get_contents($root . '/src/ValueObject/VendorTransactionStatus.php');
$policy = file_get_contents($root . '/src/Service/Policy/VendorTransactionStatusPolicy.php');
$pg = file_get_contents($root . '/migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql');
$sqlite = file_get_contents($root . '/migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql');
$entity = file_get_contents($root . '/src/Entity/VendorTransaction.php');

$checks = [
    'status catalog exists' => is_string($statusCatalog) && str_contains($statusCatalog, "public const PENDING = 'pending';"),
    'policy uses status catalog' => is_string($policy) && str_contains($policy, 'VendorTransactionStatus::PENDING') && str_contains($policy, 'VendorTransactionStatus::REFUNDED'),
    'postgres migration guards statuses' => is_string($pg) && str_contains($pg, "CHECK (status IN ('pending', 'authorized', 'failed', 'cancelled', 'settled', 'refunded'))"),
    'sqlite migration guards statuses' => is_string($sqlite) && str_contains($sqlite, "CHECK (status IN ('pending', 'authorized', 'failed', 'cancelled', 'settled', 'refunded'))"),
    'entity default uses status catalog' => is_string($entity) && str_contains($entity, 'VendorTransactionStatus::PENDING'),
];

foreach ($checks as $label => $ok) {
    if (true !== $ok) {
        fwrite(STDERR, $label . PHP_EOL);
        exit(1);
    }
}

echo "transaction status persistence smoke OK\n";
