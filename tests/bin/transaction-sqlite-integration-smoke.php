<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);

if (!isset($composer['scripts']['test:transaction-sqlite-integration'])) {
    fwrite(STDERR, "Missing composer script test:transaction-sqlite-integration\n");
    exit(1);
}

$requiredFiles = [
    $root.'/tests/Integration/Transaction/VendorTransactionSqliteIntegrationTest.php',
    $root.'/tests/Support/Transaction/DoctrineEntityManagerFactory.php',
    $root.'/tests/Support/Transaction/DoctrineBackedVendorTransactionRepository.php',
];

foreach ($requiredFiles as $requiredFile) {
    if (!is_file($requiredFile)) {
        fwrite(STDERR, sprintf("Missing sqlite integration artifact: %s\n", str_replace($root.'/', '', $requiredFile)));
        exit(1);
    }
}

$testCode = (string) file_get_contents($root.'/tests/Integration/Transaction/VendorTransactionSqliteIntegrationTest.php');
foreach (['SchemaTool', 'pdo_sqlite', 'duplicate_transaction', 'authorized'] as $needle) {
    if (!str_contains($testCode, $needle)) {
        fwrite(STDERR, sprintf("Sqlite integration test must contain %s\n", $needle));
        exit(1);
    }
}

fwrite(STDOUT, "transaction sqlite integration smoke passed\n");
