<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2);
$composer = vendoring_load_composer_json($root);

if (!vendoring_has_script($composer, 'test:transaction-sqlite-integration')) {
    fwrite(STDERR, "Missing composer script test:transaction-sqlite-integration\n");
    exit(1);
}

$requiredFiles = [
    $root . '/tests/Integration/Transaction/VendorTransactionSqliteIntegrationTest.php',
    $root . '/tests/Support/Transaction/DoctrineEntityManagerFactory.php',
    $root . '/tests/Support/Transaction/DoctrineBackedVendorTransactionRepository.php',
];

foreach ($requiredFiles as $requiredFile) {
    if (!is_file($requiredFile)) {
        fwrite(STDERR, sprintf("Missing sqlite integration artifact: %s\n", str_replace($root . '/', '', $requiredFile)));
        exit(1);
    }
}

$testCode = (string) file_get_contents($root . '/tests/Integration/Transaction/VendorTransactionSqliteIntegrationTest.php');
foreach (['SchemaTool', 'pdo_sqlite', 'duplicate_transaction', 'authorized'] as $needle) {
    if (!str_contains($testCode, $needle)) {
        fwrite(STDERR, sprintf("Sqlite integration test must contain %s\n", $needle));
        exit(1);
    }
}

fwrite(STDOUT, "transaction sqlite integration smoke passed\n");
