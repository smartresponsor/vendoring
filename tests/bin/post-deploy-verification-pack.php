<?php

declare(strict_types=1);

use App\Tests\Support\Runtime\KernelRuntimeHarness;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$projectRoot = dirname(__DIR__, 2);

$requiredDocs = [
    'docs/release/RC_BASELINE.md',
    'docs/release/RC_RUNTIME_SURFACES.md',
    'docs/release/RC_OPERATOR_SURFACE.md',
    'docs/PHASE59_SYNTHETIC_RUNTIME_PROBES.md',
];

foreach ($requiredDocs as $relativePath) {
    $absolutePath = $projectRoot.'/'.$relativePath;

    if (!is_file($absolutePath)) {
        throw new RuntimeException(sprintf('Post-deploy verification pack missing required document: %s', $relativePath));
    }
}

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDOUT, "post-deploy verification pack skipped: pdo_sqlite extension is not available\n");
    exit(0);
}

$kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase($projectRoot);

try {
    $token = KernelRuntimeHarness::seedActiveApiKey($kernel, 'write:transactions');
    $authHeaders = ['Authorization' => 'Bearer '.$token];
    $suffix = bin2hex(random_bytes(4));
    $vendorId = 'post-deploy-vendor-'.$suffix;
    $orderId = 'post-deploy-order-'.$suffix;
    $correlationId = 'post-deploy-verification-pack';

    $createResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'POST',
        '/api/vendor-transactions',
        [
            'vendorId' => $vendorId,
            'orderId' => $orderId,
            'projectId' => 'post-deploy-check',
            'amount' => '18.25',
        ],
        ['X-Correlation-ID' => $correlationId] + $authHeaders
    );

    if (201 !== $createResponse->getStatusCode()) {
        throw new RuntimeException('Post-deploy verification create did not return 201.');
    }

    if ('1' !== $createResponse->headers->get('X-API-Version')) {
        throw new RuntimeException('Post-deploy verification create did not expose X-API-Version header.');
    }

    if ($createResponse->headers->get('X-Correlation-ID') !== $correlationId) {
        throw new RuntimeException('Post-deploy verification create did not round-trip correlation id.');
    }

    if (null === $createResponse->headers->get('X-RateLimit-Limit')) {
        throw new RuntimeException('Post-deploy verification create did not expose rate-limit headers.');
    }

    $createPayload = KernelRuntimeHarness::decodeJson($createResponse);
    $transactionId = $createPayload['id'] ?? null;

    if (!is_int($transactionId) && !is_string($transactionId)) {
        throw new RuntimeException('Post-deploy verification create did not yield transaction id.');
    }

    $listResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'GET',
        '/api/vendor-transactions/vendor/'.$vendorId,
        null,
        ['X-Correlation-ID' => $correlationId] + $authHeaders
    );

    if (200 !== $listResponse->getStatusCode()) {
        throw new RuntimeException('Post-deploy verification list did not return 200.');
    }

    if ('1' !== $listResponse->headers->get('X-API-Version')) {
        throw new RuntimeException('Post-deploy verification list did not expose X-API-Version header.');
    }

    $listPayload = KernelRuntimeHarness::decodeJson($listResponse);
    $rows = $listPayload['data'] ?? [];
    $found = false;

    if (is_array($rows)) {
        foreach ($rows as $row) {
            if (is_array($row) && ($row['orderId'] ?? null) === $orderId) {
                $found = true;
                break;
            }
        }
    }

    if (!$found) {
        throw new RuntimeException('Post-deploy verification list did not expose created transaction.');
    }

    $updateResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'POST',
        '/api/vendor-transactions/vendor/'.$vendorId.'/'.(string) $transactionId.'/status',
        ['status' => 'authorized'],
        ['X-Correlation-ID' => $correlationId] + $authHeaders
    );

    if (200 !== $updateResponse->getStatusCode()) {
        throw new RuntimeException('Post-deploy verification update did not return 200.');
    }

    if ('1' !== $updateResponse->headers->get('X-API-Version')) {
        throw new RuntimeException('Post-deploy verification update did not expose X-API-Version header.');
    }

    $updatePayload = KernelRuntimeHarness::decodeJson($updateResponse);

    if (($updatePayload['status'] ?? null) !== 'authorized') {
        throw new RuntimeException('Post-deploy verification update did not authorize the transaction.');
    }

    fwrite(STDOUT, "post-deploy verification pack OK\n");
} finally {
    KernelRuntimeHarness::cleanupRuntimeState($kernel);
}
