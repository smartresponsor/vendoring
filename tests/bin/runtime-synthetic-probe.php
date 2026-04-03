<?php

declare(strict_types=1);

use App\Tests\Support\Runtime\KernelRuntimeHarness;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$projectRoot = dirname(__DIR__, 2);

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDOUT, "runtime synthetic probe skipped: pdo_sqlite extension is not available\n");
    exit(0);
}

$kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase($projectRoot);

try {
    $token = KernelRuntimeHarness::seedActiveApiKey($kernel, 'write:transactions');
    $authHeaders = ['Authorization' => 'Bearer '.$token];
    $suffix = bin2hex(random_bytes(4));
    $vendorId = 'probe-vendor-'.$suffix;
    $orderId = 'probe-order-'.$suffix;

    $createResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'POST',
        '/api/vendor-transactions',
        [
            'vendorId' => $vendorId,
            'orderId' => $orderId,
            'projectId' => 'synthetic-runtime-probe',
            'amount' => '12.50',
        ],
        ['X-Correlation-ID' => 'synthetic-runtime-probe'] + $authHeaders
    );

    if (201 !== $createResponse->getStatusCode()) {
        throw new RuntimeException('Synthetic probe create did not return 201.');
    }

    $createPayload = KernelRuntimeHarness::decodeJson($createResponse);

    if (($createPayload['status'] ?? null) !== 'pending') {
        throw new RuntimeException('Synthetic probe create did not produce pending status.');
    }

    $transactionId = $createPayload['id'] ?? null;

    if (!is_int($transactionId) && !is_string($transactionId)) {
        throw new RuntimeException('Synthetic probe did not receive a transaction id.');
    }

    $listResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'GET',
        '/api/vendor-transactions/vendor/'.$vendorId,
        null,
        ['X-Correlation-ID' => 'synthetic-runtime-probe'] + $authHeaders
    );

    if (200 !== $listResponse->getStatusCode()) {
        throw new RuntimeException('Synthetic probe list did not return 200.');
    }

    $listPayload = KernelRuntimeHarness::decodeJson($listResponse);
    $rows = $listPayload['data'] ?? [];
    $found = false;

    if (is_array($rows)) {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            if (($row['orderId'] ?? null) === $orderId) {
                $found = true;
                break;
            }
        }
    }

    if (!$found) {
        throw new RuntimeException('Synthetic probe list did not include the created transaction.');
    }

    $updateResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'POST',
        '/api/vendor-transactions/vendor/'.$vendorId.'/'.(string) $transactionId.'/status',
        ['status' => 'authorized'],
        ['X-Correlation-ID' => 'synthetic-runtime-probe'] + $authHeaders
    );

    if (200 !== $updateResponse->getStatusCode()) {
        throw new RuntimeException('Synthetic probe status update did not return 200.');
    }

    $updatePayload = KernelRuntimeHarness::decodeJson($updateResponse);

    if (($updatePayload['status'] ?? null) !== 'authorized') {
        throw new RuntimeException('Synthetic probe status update did not authorize the transaction.');
    }

    echo "runtime synthetic probe OK\n";
} finally {
    KernelRuntimeHarness::cleanupRuntimeState($kernel);
}
