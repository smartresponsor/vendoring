<?php

declare(strict_types=1);

use App\Tests\Support\Runtime\KernelRuntimeHarness;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$projectRoot = dirname(__DIR__, 2);

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDOUT, "payout processing synthetic probe skipped: pdo_sqlite extension is not available\n");
    exit(0);
}

$kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase($projectRoot);

try {
    $suffix = bin2hex(random_bytes(4));
    $correlationId = 'payout-processing-synthetic-probe';
    $vendorId = 'probe-vendor-' . $suffix;

    $createResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'POST',
        '/api/payout/create',
        [
            'vendorId' => $vendorId,
            'currency' => 'USD',
            'thresholdCents' => 0,
            'feeRate' => 0.10,
        ],
        ['X-Correlation-ID' => $correlationId],
    );

    if (201 !== $createResponse->getStatusCode()) {
        throw new RuntimeException('Payout processing synthetic probe create did not return 201.');
    }

    $createPayload = KernelRuntimeHarness::decodeJson($createResponse);
    if (($createPayload['data']['created'] ?? null) != true) {
        throw new RuntimeException('Payout processing synthetic probe did not mark payout as created.');
    }

    $payoutId = $createPayload['data']['payoutId'] ?? null;
    if (!is_string($payoutId) || '' === $payoutId) {
        throw new RuntimeException('Payout processing synthetic probe did not return payout id.');
    }

    $getResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'GET',
        '/api/payout/' . $payoutId,
        null,
        ['X-Correlation-ID' => $correlationId],
    );

    if (200 !== $getResponse->getStatusCode()) {
        throw new RuntimeException('Payout processing synthetic probe getOne did not return 200.');
    }

    $getPayload = KernelRuntimeHarness::decodeJson($getResponse);
    if (($getPayload['data']['status'] ?? null) !== 'pending') {
        throw new RuntimeException('Payout processing synthetic probe did not expose pending payout before processing.');
    }

    $processResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'POST',
        '/api/payout/process/' . $payoutId,
        [],
        ['X-Correlation-ID' => $correlationId],
    );

    if (200 !== $processResponse->getStatusCode()) {
        throw new RuntimeException('Payout processing synthetic probe process did not return 200.');
    }

    $processPayload = KernelRuntimeHarness::decodeJson($processResponse);
    if (($processPayload['data']['processed'] ?? null) != true) {
        throw new RuntimeException('Payout processing synthetic probe did not mark payout as processed.');
    }

    $afterResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'GET',
        '/api/payout/' . $payoutId,
        null,
        ['X-Correlation-ID' => $correlationId],
    );

    if (200 !== $afterResponse->getStatusCode()) {
        throw new RuntimeException('Payout processing synthetic probe getOne after process did not return 200.');
    }

    $afterPayload = KernelRuntimeHarness::decodeJson($afterResponse);
    if (($afterPayload['data']['status'] ?? null) !== 'processed') {
        throw new RuntimeException('Payout processing synthetic probe did not expose processed payout after processing.');
    }

    fwrite(STDOUT, "payout processing synthetic probe OK\n");
} finally {
    KernelRuntimeHarness::cleanupRuntimeState($kernel);
}
