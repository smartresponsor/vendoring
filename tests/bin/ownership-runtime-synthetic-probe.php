<?php

declare(strict_types=1);

use App\Vendoring\Tests\Support\Runtime\KernelRuntimeHarness;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$projectRoot = dirname(__DIR__, 2);

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDOUT, "ownership runtime synthetic probe skipped: pdo_sqlite extension is not available\n");
    exit(0);
}

$kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase($projectRoot);

try {
    $vendor = KernelRuntimeHarness::seedActiveVendor($kernel, 'Ownership Probe Vendor');
    $vendorId = $vendor->getId();

    if (!is_int($vendorId)) {
        throw new RuntimeException('Ownership probe expected numeric vendor id.');
    }

    $requests = [
        ['/api/vendor-ownership/vendor/' . $vendorId . '/payments', [
            'providerCode' => 'stripe',
            'methodCode' => 'card',
            'externalPaymentId' => 'pm_probe_1',
            'label' => 'Primary card',
            'status' => 'active',
            'isDefault' => true,
            'meta' => ['source' => 'ownership_probe'],
        ]],
        ['/api/vendor-ownership/vendor/' . $vendorId . '/commissions', [
            'code' => 'marketplace',
            'direction' => 'debit',
            'ratePercent' => '7.5',
            'status' => 'active',
            'changedByUserId' => 9001,
            'reason' => 'ownership_probe',
            'meta' => ['source' => 'ownership_probe'],
        ]],
        ['/api/vendor-ownership/vendor/' . $vendorId . '/conversations', [
            'subject' => 'Ownership probe conversation',
            'channel' => 'chat',
            'counterpartyType' => 'customer',
            'counterpartyId' => 'customer-probe-1',
            'counterpartyName' => 'Customer Probe',
            'status' => 'open',
            'conversationMeta' => ['source' => 'ownership_probe'],
            'firstMessageBody' => 'Hello from ownership probe',
            'firstMessageDirection' => 'outbound',
            'externalMessageId' => 'ownership-probe-message-1',
            'messageMeta' => ['source' => 'ownership_probe'],
        ]],
        ['/api/vendor-ownership/vendor/' . $vendorId . '/shipments', [
            'externalShipmentId' => 'shp_probe_1',
            'carrierCode' => 'ups',
            'methodCode' => 'ground',
            'trackingNumber' => 'TRACKPROBE1',
            'status' => 'shipped',
            'meta' => ['source' => 'ownership_probe'],
        ]],
        ['/api/vendor-ownership/vendor/' . $vendorId . '/groups', [
            'code' => 'preferred',
            'name' => 'Preferred Vendors',
            'status' => 'active',
            'meta' => ['source' => 'ownership_probe'],
        ]],
        ['/api/vendor-ownership/vendor/' . $vendorId . '/categories', [
            'categoryCode' => 'electronics',
            'categoryName' => 'Electronics',
            'isPrimary' => true,
        ]],
        ['/api/vendor-ownership/vendor/' . $vendorId . '/favourites', [
            'targetType' => 'product',
            'targetId' => 'product-probe-1',
            'note' => 'probe favourite',
        ]],
        ['/api/vendor-ownership/vendor/' . $vendorId . '/wishlists', [
            'customerReference' => 'customer-probe-1',
            'name' => 'Probe wishlist',
            'status' => 'active',
            'targetType' => 'product',
            'targetId' => 'product-probe-1',
            'quantity' => 2,
            'note' => 'probe wishlist item',
        ]],
        ['/api/vendor-ownership/vendor/' . $vendorId . '/codes', [
            'code' => 'OTP-PROBE-1',
            'purpose' => 'login',
            'expiresAt' => '2030-01-01T00:00:00+00:00',
            'phone' => '+15550000001',
            'isLogin' => true,
        ]],
        ['/api/vendor-ownership/vendor/' . $vendorId . '/remember-me-tokens', [
            'series' => 'series-probe-1',
            'tokenValue' => 'token-probe-1',
            'providerClass' => 'App\\Vendoring\\Security\\VendorRememberMeProvider',
            'username' => 'probe.vendor',
        ]],
        ['/api/vendor-ownership/vendor/' . $vendorId . '/customer-orders', [
            'externalOrderId' => 'order-probe-1',
            'status' => 'placed',
            'currency' => 'USD',
            'grossCents' => 1599,
            'netCents' => 1399,
            'orderNumber' => 'ORDER-PROBE-1',
            'meta' => ['source' => 'ownership_probe'],
        ]],
    ];

    foreach ($requests as [$uri, $payload]) {
        $response = KernelRuntimeHarness::requestJson($kernel, 'POST', $uri, $payload);

        if (200 !== $response->getStatusCode()) {
            throw new RuntimeException('Ownership probe mutation did not return 200 for ' . $uri);
        }
    }

    $viewResponse = KernelRuntimeHarness::requestJson($kernel, 'GET', '/api/vendor-ownership/vendor/' . $vendorId);

    if (200 !== $viewResponse->getStatusCode()) {
        throw new RuntimeException('Ownership probe view did not return 200.');
    }

    $payload = KernelRuntimeHarness::decodeJson($viewResponse);
    $counts = $payload['data']['relationCounts'] ?? null;

    if (!is_array($counts)) {
        throw new RuntimeException('Ownership probe did not expose relation counts.');
    }

    foreach ([
        'payments' => 1,
        'commissions' => 1,
        'commissionHistory' => 1,
        'conversations' => 1,
        'conversationMessages' => 1,
        'shipments' => 1,
        'groups' => 1,
        'categories' => 1,
        'favourites' => 1,
        'wishlists' => 1,
        'wishlistItems' => 1,
        'codes' => 1,
        'rememberMeTokens' => 1,
        'customerOrders' => 1,
    ] as $key => $expected) {
        if (($counts[$key] ?? null) !== $expected) {
            throw new RuntimeException(sprintf('Ownership probe expected %s=%d, got %s.', $key, $expected, var_export($counts[$key] ?? null, true)));
        }
    }

    if (($counts['logs'] ?? 0) < 10) {
        throw new RuntimeException('Ownership probe expected mutation audit logs to be recorded.');
    }

    echo "ownership runtime synthetic probe OK\n";
} finally {
    KernelRuntimeHarness::cleanupRuntimeState($kernel);
}
