<?php

declare(strict_types=1);

use App\Tests\Support\Runtime\KernelRuntimeHarness;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDOUT, "fresh db boot smoke skipped: pdo_sqlite is required\n");
    exit(0);
}

$kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 2), environment: 'prod', debug: false);
$response = KernelRuntimeHarness::requestJson($kernel, 'GET', '/api/vendor-transactions/vendor/vendor-fresh');
$payload = KernelRuntimeHarness::decodeJson($response);
$kernel->shutdown();

if (200 !== $response->getStatusCode() || ($payload['data'] ?? null) !== []) {
    fwrite(STDERR, "fresh db boot smoke failed\n");
    exit(1);
}

fwrite(STDOUT, "fresh db boot smoke passed\n");
