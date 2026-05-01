<?php

declare(strict_types=1);

use App\Vendoring\Tests\Support\Runtime\KernelRuntimeHarness;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDOUT, "fresh db boot smoke skipped: pdo_sqlite is required\n");
    exit(0);
}

$kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 2), environment: 'prod', debug: false);

try {
    $transactionResponse = KernelRuntimeHarness::requestJson($kernel, 'GET', '/api/vendor-transactions/vendor/vendor-fresh');
    $transactionPayload = KernelRuntimeHarness::decodeJson($transactionResponse);

    if (200 !== $transactionResponse->getStatusCode() || ($transactionPayload['data'] ?? null) !== []) {
        fwrite(STDERR, "fresh db boot smoke failed for vendor transactions route\n");
        exit(1);
    }

    $vendor = KernelRuntimeHarness::seedActiveVendor($kernel, 'Fresh Boot Ownership VendorEntity');
    $ownershipResponse = KernelRuntimeHarness::requestJson($kernel, 'GET', '/api/vendor-ownership/vendor/' . (string) $vendor->getId());
    $ownershipPayload = KernelRuntimeHarness::decodeJson($ownershipResponse);

    if (200 !== $ownershipResponse->getStatusCode()) {
        fwrite(STDERR, "fresh db boot smoke failed for vendor ownership route\n");
        exit(1);
    }

    $counts = $ownershipPayload['data']['relationCounts'] ?? null;

    if (!is_array($counts)) {
        fwrite(STDERR, "fresh db boot smoke did not expose ownership relation counts\n");
        exit(1);
    }

    foreach ([
        'payments',
        'commissions',
        'commissionHistory',
        'conversations',
        'conversationMessages',
        'shipments',
        'groups',
        'categories',
        'favourites',
        'wishlists',
        'wishlistItems',
        'codes',
        'rememberMeTokens',
        'customerOrders',
        'logs',
    ] as $key) {
        if (($counts[$key] ?? null) !== 0) {
            fwrite(STDERR, sprintf("fresh db boot smoke expected zero ownership count for %s\n", $key));
            exit(1);
        }
    }
} finally {
    KernelRuntimeHarness::cleanupRuntimeState($kernel);
}

fwrite(STDOUT, "fresh db boot smoke passed\n");
