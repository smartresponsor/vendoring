<?php

declare(strict_types=1);

use App\Vendoring\Tests\Support\Runtime\KernelRuntimeHarness;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$projectRoot = dirname(__DIR__, 2);

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDOUT, "write api auth contract smoke skipped: pdo_sqlite extension is not available\n");
    exit(0);
}

$kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase($projectRoot);

try {
    $missingAuthResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor-transactions', [
        'vendorId' => 'auth-vendor',
        'orderId' => 'auth-order-missing',
        'amount' => '10.00',
    ]);
    $missingAuthPayload = KernelRuntimeHarness::decodeJson($missingAuthResponse);

    if (401 !== $missingAuthResponse->getStatusCode() || ($missingAuthPayload['error'] ?? null) !== 'authentication_required') {
        throw new RuntimeException('Write API auth contract smoke expected authentication_required on missing Authorization header.');
    }

    if ('write:transactions' !== $missingAuthResponse->headers->get('X-Auth-Required-Permission')) {
        throw new RuntimeException('Write API auth contract smoke expected X-Auth-Required-Permission header.');
    }

    $readOnlyToken = KernelRuntimeHarness::seedActiveApiKey($kernel, 'read:transactions');
    $readOnlyResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor-transactions', [
        'vendorId' => 'auth-vendor',
        'orderId' => 'auth-order-read-only',
        'amount' => '10.00',
    ], ['Authorization' => 'Bearer ' . $readOnlyToken]);
    $readOnlyPayload = KernelRuntimeHarness::decodeJson($readOnlyResponse);

    if (403 !== $readOnlyResponse->getStatusCode() || ($readOnlyPayload['error'] ?? null) !== 'permission_denied') {
        throw new RuntimeException('Write API auth contract smoke expected permission_denied on under-scoped token.');
    }

    $writeToken = KernelRuntimeHarness::seedActiveApiKey($kernel, 'write:transactions');
    $writeResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor-transactions', [
        'vendorId' => 'auth-vendor',
        'orderId' => 'auth-order-write',
        'amount' => '10.00',
    ], ['Authorization' => 'Bearer ' . $writeToken]);
    $writePayload = KernelRuntimeHarness::decodeJson($writeResponse);

    if (201 !== $writeResponse->getStatusCode() || ($writePayload['status'] ?? null) !== 'pending') {
        throw new RuntimeException('Write API auth contract smoke expected authenticated create to succeed.');
    }

    fwrite(STDOUT, "write api auth contract smoke passed\n");
} finally {
    KernelRuntimeHarness::cleanupRuntimeState($kernel);
}
