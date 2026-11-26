<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Runtime;

use App\Vendoring\Tests\Support\Runtime\KernelRuntimeHarness;
use PHPUnit\Framework\TestCase;

final class VendorCanaryRolloutAuthorizationTest extends TestCase
{
    public function testHttpRolloutUsesPersistedScopeAndHidesGlobalManifest(): void
    {
        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));
        try {
            $token = KernelRuntimeHarness::seedFinancialApiKey($kernel, 'tenant-a', 'shared-vendor', 'read:rollout', $canonical);
            $other = KernelRuntimeHarness::seedFinancialApiKey($kernel, 'tenant-b', 'shared-vendor', 'read:rollout', $otherCanonical);
            $readOnly = KernelRuntimeHarness::seedFinancialApiKey($kernel, 'tenant-c', 'shared-vendor', 'read:finance');
            $base = '/api/vendor/canary/rollout/overview?flagName=test';
            $headers = ['Authorization' => 'Bearer '.$token];
            self::assertSame(403, KernelRuntimeHarness::requestJson($kernel, 'GET', $base)->getStatusCode());
            self::assertSame(403, KernelRuntimeHarness::requestJson($kernel, 'GET', $base.'&tenantId=tenant-b', null, $headers)->getStatusCode());
            self::assertSame(403, KernelRuntimeHarness::requestJson($kernel, 'GET', $base.'&vendorId=foreign', null, $headers)->getStatusCode());
            self::assertSame(403, KernelRuntimeHarness::requestJson($kernel, 'GET', $base, null, ['Authorization' => 'Bearer '.$readOnly])->getStatusCode());
            $response = KernelRuntimeHarness::requestJson($kernel, 'GET', $base.'&tenantId=tenant-a&vendorId=shared-vendor', null, $headers);
            self::assertSame(200, $response->getStatusCode());
            $data = KernelRuntimeHarness::decodeJson($response)['data'];
            self::assertIsArray($data);
            self::assertSame('tenant-a', $data['tenantId']);
            self::assertSame($canonical, $data['vendorId']);
            self::assertArrayNotHasKey('manifest', $data);
            self::assertArrayNotHasKey('rollback', $data);
            $second = KernelRuntimeHarness::requestJson($kernel, 'GET', $base, null, ['Authorization' => 'Bearer '.$other]);
            self::assertSame(200, $second->getStatusCode());
            $dataB = KernelRuntimeHarness::decodeJson($second)['data'];
            self::assertIsArray($dataB);
            self::assertSame('tenant-b', $dataB['tenantId']);
            self::assertSame($otherCanonical, $dataB['vendorId']);
            self::assertNotSame($data['vendorId'], $dataB['vendorId']);
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }
}
