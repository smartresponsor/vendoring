<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2);
$composerJsonPath = $root . '/composer.json';
$repoInterfacePath = $root . '/src/RepositoryInterface/VendorTransactionRepositoryInterface.php';
$repoPath = $root . '/src/Repository/VendorTransactionRepository.php';
$managerPath = $root . '/src/Service/VendorTransactionManager.php';
$controllerPath = $root . '/src/Controller/VendorTransactionController.php';
$pgMigrationPath = $root . '/migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql';
$sqliteMigrationPath = $root . '/migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql';

$composerJson = vendoring_load_composer_json($root);
$scripts = vendoring_composer_section($composerJson, 'scripts');
if (!array_key_exists('test:transaction-idempotency', $scripts)) {
    fwrite(STDERR, "composer script test:transaction-idempotency missing\n");
    exit(1);
}

foreach ([$repoInterfacePath, $repoPath, $managerPath, $controllerPath, $pgMigrationPath, $sqliteMigrationPath] as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, sprintf("required file missing: %s\n", $path));
        exit(1);
    }
}

$repoInterface = (string) file_get_contents($repoInterfacePath);
$repo = (string) file_get_contents($repoPath);
$manager = (string) file_get_contents($managerPath);
$controller = (string) file_get_contents($controllerPath);
$pgSql = (string) file_get_contents($pgMigrationPath);
$sqliteSql = (string) file_get_contents($sqliteMigrationPath);

if (!str_contains($repoInterface, 'existsForVendorOrderProject')) {
    fwrite(STDERR, "idempotency repository method missing in interface\n");
    exit(1);
}

if (!str_contains($repo, 'transaction.projectId IS NULL')) {
    fwrite(STDERR, "null-project idempotency query branch missing in concrete repository\n");
    exit(1);
}

if (!str_contains($manager, 'duplicate_transaction')) {
    fwrite(STDERR, "duplicate transaction guard missing in manager\n");
    exit(1);
}

if (!str_contains($manager, 'normalizeProjectId')) {
    fwrite(STDERR, "projectId normalization missing in manager\n");
    exit(1);
}

if (!str_contains($controller, '? 409 : 422')) {
    fwrite(STDERR, "controller duplicate_transaction mapping missing\n");
    exit(1);
}

foreach ([$pgSql, $sqliteSql] as $sql) {
    if (!str_contains($sql, 'uniq_vendor_transaction_vendor_order_project_nonnull')) {
        fwrite(STDERR, "nonnull project unique index missing\n");
        exit(1);
    }

    if (!str_contains($sql, 'uniq_vendor_transaction_vendor_order_nullproject')) {
        fwrite(STDERR, "null project unique index missing\n");
        exit(1);
    }
}

echo "transaction idempotency smoke OK\n";
