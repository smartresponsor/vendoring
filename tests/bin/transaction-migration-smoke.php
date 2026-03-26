<?php

declare(strict_types=1);

require_once __DIR__.'/_composer_json.php';

$root = dirname(__DIR__, 2);
$composerJsonPath = $root.'/composer.json';
$pgMigrationPath = $root.'/migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql';
$sqliteMigrationPath = $root.'/migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql';

if (!is_file($composerJsonPath)) {
    fwrite(STDERR, "composer.json missing\n");
    exit(1);
}

$composerJson = vendoring_load_composer_json($root);
$scripts = vendoring_composer_section($composerJson, 'scripts');

if (!array_key_exists('test:transaction-migration', $scripts)) {
    fwrite(STDERR, "composer script test:transaction-migration missing\n");
    exit(1);
}

foreach ([$pgMigrationPath, $sqliteMigrationPath] as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, sprintf("required migration missing: %s\n", $path));
        exit(1);
    }

    $sql = (string) file_get_contents($path);
    if (!str_contains($sql, 'CREATE TABLE vendor_transaction')) {
        fwrite(STDERR, sprintf("vendor_transaction create statement missing in %s\n", $path));
        exit(1);
    }
}

echo "transaction migration smoke OK\n";
