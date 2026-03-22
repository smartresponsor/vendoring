<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
$manager = (string) file_get_contents($root.'/src/Service/VendorTransactionManager.php');
$controller = (string) file_get_contents($root.'/src/Controller/VendorTransactionController.php');
$services = (string) file_get_contents($root.'/config/services.yaml');
$pgMigration = (string) file_get_contents($root.'/migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql');
$sqliteMigration = (string) file_get_contents($root.'/migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql');

if (!isset($composer['scripts']['test:transaction-amount'])) {
    fwrite(STDERR, "Missing composer script test:transaction-amount\n");
    exit(1);
}

foreach ([
    $root.'/src/ServiceInterface/Policy/VendorTransactionAmountPolicyInterface.php',
    $root.'/src/Service/Policy/VendorTransactionAmountPolicy.php',
    $root.'/tests/Unit/Policy/VendorTransactionAmountPolicyTest.php',
] as $path) {
    if (!file_exists($path)) {
        fwrite(STDERR, sprintf("Missing required transaction amount file: %s\n", $path));
        exit(1);
    }
}

if (!str_contains($manager, 'VendorTransactionAmountPolicyInterface')) {
    fwrite(STDERR, "VendorTransactionManager must depend on VendorTransactionAmountPolicyInterface.\n");
    exit(1);
}

if (!str_contains($manager, 'amount: $this->amountPolicy->normalize($data->amount)')) {
    fwrite(STDERR, "VendorTransactionManager must normalize transaction amount before entity creation.\n");
    exit(1);
}

if (!str_contains($controller, 'catch (\\InvalidArgumentException $exception)')) {
    fwrite(STDERR, "VendorTransactionController must map invalid create amount/input to 422.\n");
    exit(1);
}

if (!str_contains($services, "App\\ServiceInterface\\Policy\\VendorTransactionAmountPolicyInterface: '@App\\Service\\Policy\\VendorTransactionAmountPolicy'")) {
    fwrite(STDERR, "services.yaml must alias VendorTransactionAmountPolicyInterface to VendorTransactionAmountPolicy.\n");
    exit(1);
}

foreach ([$pgMigration, $sqliteMigration] as $sql) {
    if (!str_contains($sql, 'CHECK (amount > 0)')) {
        fwrite(STDERR, "Transaction migrations must guard positive amount with CHECK constraint.\n");
        exit(1);
    }
}

fwrite(STDOUT, "transaction amount smoke OK\n");
