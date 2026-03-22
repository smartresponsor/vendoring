<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);

if (!isset($composer['scripts']['test:transaction-persistence'])) {
    fwrite(STDERR, "Missing composer script test:transaction-persistence\n");
    exit(1);
}

$migrationPath = $root.'/migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql';
if (!is_file($migrationPath)) {
    fwrite(STDERR, "Missing SQLite transaction migration\n");
    exit(1);
}

$repositoryPath = $root.'/src/Repository/VendorTransactionRepository.php';
if (!is_file($repositoryPath)) {
    fwrite(STDERR, "Missing VendorTransactionRepository.php\n");
    exit(1);
}

$sql = (string) file_get_contents($migrationPath);
if (!str_contains($sql, 'CREATE TABLE vendor_transaction')) {
    fwrite(STDERR, "SQLite migration must create vendor_transaction table\n");
    exit(1);
}

foreach (['vendor_id', 'order_id', 'project_id', 'amount', 'status', 'created_at'] as $column) {
    if (!str_contains($sql, $column)) {
        fwrite(STDERR, sprintf("SQLite migration must define column %s\n", $column));
        exit(1);
    }
}

$repositoryCode = (string) file_get_contents($repositoryPath);
if (!str_contains($repositoryCode, "['createdAt' => 'DESC', 'id' => 'DESC']")) {
    fwrite(STDERR, "VendorTransactionRepository must keep newest-first ordering\n");
    exit(1);
}

if (extension_loaded('pdo_sqlite')) {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec($sql);

    $insert = $pdo->prepare('INSERT INTO vendor_transaction (vendor_id, order_id, project_id, amount, status, created_at) VALUES (:vendor_id, :order_id, :project_id, :amount, :status, :created_at)');
    $insert->execute([
        ':vendor_id' => 'vendor-1',
        ':order_id' => 'order-1',
        ':project_id' => 'project-1',
        ':amount' => '10.50',
        ':status' => 'pending',
        ':created_at' => '2026-03-21 00:00:00',
    ]);
    $insert->execute([
        ':vendor_id' => 'vendor-1',
        ':order_id' => 'order-2',
        ':project_id' => null,
        ':amount' => '12.00',
        ':status' => 'authorized',
        ':created_at' => '2026-03-21 01:00:00',
    ]);

    $pdo->prepare('UPDATE vendor_transaction SET status = :status WHERE vendor_id = :vendor_id AND order_id = :order_id')
        ->execute([
            ':status' => 'settled',
            ':vendor_id' => 'vendor-1',
            ':order_id' => 'order-2',
        ]);

    $rows = $pdo->query('SELECT vendor_id, order_id, status, amount FROM vendor_transaction WHERE vendor_id = "vendor-1" ORDER BY created_at DESC, id DESC')?->fetchAll(PDO::FETCH_ASSOC);

    if (!is_array($rows) || 2 !== count($rows)) {
        fwrite(STDERR, "Unexpected vendor_transaction row count\n");
        exit(1);
    }

    if (($rows[0]['order_id'] ?? null) !== 'order-2' || ($rows[0]['status'] ?? null) !== 'settled') {
        fwrite(STDERR, "Newest transaction row must be order-2 with settled status\n");
        exit(1);
    }

    if (($rows[1]['order_id'] ?? null) !== 'order-1' || ($rows[1]['status'] ?? null) !== 'pending') {
        fwrite(STDERR, "Older transaction row must remain order-1 with pending status\n");
        exit(1);
    }

    fwrite(STDOUT, "transaction persistence smoke passed with sqlite runtime\n");
    exit(0);
}

fwrite(STDOUT, "transaction persistence smoke passed in static-contract mode (pdo_sqlite unavailable)\n");
