<?php

declare(strict_types=1);

use App\Tests\Support\Runtime\KernelRuntimeHarness;

require dirname(__DIR__, 2).'/vendor/autoload.php';

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDOUT, "transaction kernel runtime smoke skipped: pdo_sqlite is required\n");
    exit(0);
}

$projectRoot = dirname(__DIR__, 2);
$kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase($projectRoot);
$token = KernelRuntimeHarness::seedActiveApiKey($kernel, 'write:transactions');
$authHeaders = ['Authorization' => 'Bearer '.$token];

$createResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor-transactions', [
    'vendorId' => 'vendor-smoke',
    'orderId' => 'order-smoke',
    'projectId' => null,
    'amount' => '25.00',
], $authHeaders);
$createPayload = KernelRuntimeHarness::decodeJson($createResponse);

if (201 !== $createResponse->getStatusCode() || ($createPayload['status'] ?? null) !== 'pending') {
    fwrite(STDERR, "transaction kernel runtime smoke failed during create\n");
    exit(1);
}

$listResponse = KernelRuntimeHarness::requestJson($kernel, 'GET', '/api/vendor-transactions/vendor/vendor-smoke');
$listPayload = KernelRuntimeHarness::decodeJson($listResponse);

$listData = $listPayload['data'] ?? null;

if (200 !== $listResponse->getStatusCode() || !is_array($listData) || 1 !== count($listData)) {
    fwrite(STDERR, "transaction kernel runtime smoke failed during list\n");
    exit(1);
}

$updateResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor-transactions/vendor/vendor-smoke/'.$createPayload['id'].'/status', [
    'status' => 'authorized',
], $authHeaders);
$updatePayload = KernelRuntimeHarness::decodeJson($updateResponse);

if (200 !== $updateResponse->getStatusCode() || ($updatePayload['status'] ?? null) !== 'authorized') {
    fwrite(STDERR, "transaction kernel runtime smoke failed during status update\n");
    exit(1);
}

$kernel->shutdown();

fwrite(STDOUT, "transaction kernel runtime smoke passed\n");
