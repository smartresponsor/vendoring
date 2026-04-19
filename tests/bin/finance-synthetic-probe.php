<?php

declare(strict_types=1);

use App\Vendoring\Tests\Support\Runtime\KernelRuntimeHarness;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$projectRoot = dirname(__DIR__, 2);

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDOUT, "finance synthetic probe skipped: pdo_sqlite extension is not available\n");
    exit(0);
}

$kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase($projectRoot);

try {
    $suffix = bin2hex(random_bytes(4));
    $tenantId = 'probe-tenant-' . $suffix;
    $vendorId = 'probe-vendor-' . $suffix;
    $correlationId = 'finance-synthetic-probe';

    $accountResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'POST',
        '/api/payouts/account',
        [
            'tenantId' => $tenantId,
            'vendorId' => $vendorId,
            'provider' => 'bank',
            'accountRef' => 'acct-' . $suffix,
            'currency' => 'USD',
            'active' => true,
        ],
        ['X-Correlation-ID' => $correlationId],
    );

    if (200 !== $accountResponse->getStatusCode()) {
        throw new RuntimeException('Finance synthetic probe payout account upsert did not return 200.');
    }

    $accountPayload = KernelRuntimeHarness::decodeJson($accountResponse);
    $accountData = $accountPayload['data'] ?? null;
    if (!is_array($accountData) || ($accountData['provider'] ?? null) !== 'bank') {
        throw new RuntimeException('Finance synthetic probe payout account provider mismatch.');
    }

    $statementPath = '/api/payouts/statements/' . $vendorId . '?tenantId=' . $tenantId . '&from=2026-01-01&to=2026-01-31&currency=USD';
    $statementResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'GET',
        $statementPath,
        null,
        ['X-Correlation-ID' => $correlationId],
    );

    if (200 !== $statementResponse->getStatusCode()) {
        throw new RuntimeException('Finance synthetic probe statement build did not return 200.');
    }

    $statementPayload = KernelRuntimeHarness::decodeJson($statementResponse);
    if (!array_key_exists('data', $statementPayload)) {
        throw new RuntimeException('Finance synthetic probe statement build did not return data payload.');
    }

    $exportPath = '/api/payouts/statements/' . $vendorId . '/export?tenantId=' . $tenantId . '&from=2026-01-01&to=2026-01-31&currency=USD';
    $exportResponse = KernelRuntimeHarness::requestJson(
        $kernel,
        'GET',
        $exportPath,
        null,
        ['X-Correlation-ID' => $correlationId],
    );

    if (200 !== $exportResponse->getStatusCode()) {
        throw new RuntimeException('Finance synthetic probe statement export did not return 200.');
    }

    $exportPayload = KernelRuntimeHarness::decodeJson($exportResponse);
    $exportData = $exportPayload['data'] ?? null;
    $pdfBase64 = is_array($exportData) ? ($exportData['pdfBase64'] ?? null) : null;
    if (!is_string($pdfBase64) || '' === $pdfBase64) {
        throw new RuntimeException('Finance synthetic probe statement export did not return base64 content.');
    }

    fwrite(STDOUT, 'finance synthetic probe OK
');
} finally {
    KernelRuntimeHarness::cleanupRuntimeState($kernel);
}
