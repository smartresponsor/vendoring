<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Runtime;

use App\Vendoring\Tests\Support\Runtime\KernelRuntimeHarness;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApiQueryValidationKernelRuntimeTest extends TestCase
{
    private static KernelInterface $kernel;
    private static string $financeToken;
    private static string $otherTenantToken;

    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            return;
        }

        self::$kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));
        self::$financeToken = KernelRuntimeHarness::seedFinancialApiKey(self::$kernel, 'tenant-1', 'vendor-1', 'read:finance,write:payouts');
        self::$otherTenantToken = KernelRuntimeHarness::seedFinancialApiKey(self::$kernel, 'tenant-2', 'vendor-1', 'read:finance,write:payouts');
    }

    public static function tearDownAfterClass(): void
    {
        if (!isset(self::$kernel)) {
            return;
        }

        KernelRuntimeHarness::cleanupRuntimeState(self::$kernel);
    }

    #[DataProvider('validationSurfaceProvider')]
    public function testKernelRuntimeReturnsExpectedApiValidationErrorCodes(string $uri, string $expectedError): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $response = KernelRuntimeHarness::requestJson(self::$kernel, 'GET', $uri);
        $payload = KernelRuntimeHarness::decodeJson($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame($expectedError, $payload['errorCode'] ?? null);
    }

    /**
     * @return iterable<string, array{uri: string, expectedError: string}>
     */
    public static function validationSurfaceProvider(): iterable
    {
        yield 'statement runtime requires from' => [
            'uri' => '/api/vendor/payout/statement/vendor-1?to=2026-01-31&currency=USD',
            'expectedError' => 'statement_from_required',
        ];

        yield 'statement runtime requires to' => [
            'uri' => '/api/vendor/payout/statement/vendor-1?from=2026-01-01&currency=USD',
            'expectedError' => 'statement_to_required',
        ];
    }

    public function testKernelRuntimeReturnsDedicatedErrorForOutOfRangePayoutRetentionFeePercent(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $response = KernelRuntimeHarness::requestJson(self::$kernel, 'POST', '/api/vendor/payout', [
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => 1000,
            'retentionFeePercent' => 1.5,
        ], ['Authorization' => 'Bearer '.self::$financeToken]);
        $payload = KernelRuntimeHarness::decodeJson($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('retention_fee_percent_out_of_range', $payload['errorCode'] ?? null);
    }

    public function testKernelRuntimeAcceptsTrimmedNumericPayoutPayloadValues(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $response = KernelRuntimeHarness::requestJson(self::$kernel, 'POST', '/api/vendor/payout', [
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => ' 1000 ',
            'retentionFeePercent' => ' 0.05 ',
        ], ['Authorization' => 'Bearer '.self::$financeToken]);
        $payload = KernelRuntimeHarness::decodeJson($response);
        $data = $payload['data'] ?? null;

        self::assertSame(200, $response->getStatusCode());
        self::assertIsArray($data);
        self::assertFalse($data['created'] ?? null);
        self::assertSame('threshold_not_met', $data['reason'] ?? null);
    }

    public function testKernelRuntimeMapsTrimmedOutOfRangeRetentionFeeToDedicatedValidationError(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $response = KernelRuntimeHarness::requestJson(self::$kernel, 'POST', '/api/vendor/payout', [
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => ' 1000 ',
            'retentionFeePercent' => ' 1.5 ',
        ], ['Authorization' => 'Bearer '.self::$financeToken]);
        $payload = KernelRuntimeHarness::decodeJson($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('retention_fee_percent_out_of_range', $payload['errorCode'] ?? null);
    }

    public function testKernelRuntimeTreatsWhitespaceOnlyRetentionFeeAsRequiredValidationFailure(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $response = KernelRuntimeHarness::requestJson(self::$kernel, 'POST', '/api/vendor/payout', [
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => ' 1000 ',
            'retentionFeePercent' => '   ',
        ], ['Authorization' => 'Bearer '.self::$financeToken]);
        $payload = KernelRuntimeHarness::decodeJson($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('retention_fee_percent_required', $payload['errorCode'] ?? null);
    }

    public function testKernelRuntimeTreatsWhitespaceOnlyThresholdAsRequiredValidationFailure(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $response = KernelRuntimeHarness::requestJson(self::$kernel, 'POST', '/api/vendor/payout', [
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => '   ',
            'retentionFeePercent' => '0.05',
        ], ['Authorization' => 'Bearer '.self::$financeToken]);
        $payload = KernelRuntimeHarness::decodeJson($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('threshold_cents_required', $payload['errorCode'] ?? null);
    }

    public function testUnauthenticatedAndCrossTenantPayoutRequestsAreDenied(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite required');
        }
        $payload = ['vendorId' => 'vendor-1', 'tenantId' => 'tenant-1', 'currency' => 'USD', 'thresholdCents' => 1000, 'retentionFeePercent' => 0.05];
        $unauthorized = KernelRuntimeHarness::requestJson(self::$kernel, 'POST', '/api/vendor/payout', $payload);
        self::assertSame(403, $unauthorized->getStatusCode());
        $wrong = KernelRuntimeHarness::requestJson(self::$kernel, 'POST', '/api/vendor/payout', $payload, ['Authorization' => 'Bearer '.self::$otherTenantToken]);
        self::assertSame(403, $wrong->getStatusCode());
        $right = KernelRuntimeHarness::requestJson(self::$kernel, 'POST', '/api/vendor/payout', $payload, ['Authorization' => 'Bearer '.self::$financeToken]);
        self::assertSame(200, $right->getStatusCode());
    }

    public function testSameVendorAcrossTenantsReadsOnlyAuthorizedLedgerAndPeriod(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite required');
        }
        KernelRuntimeHarness::seedFinancialLedgerEntry(self::$kernel, 'tenant-1', 'vendor-1', 125.0, '2026-01-31 12:00:00');
        KernelRuntimeHarness::seedFinancialLedgerEntry(self::$kernel, 'tenant-2', 'vendor-1', 500.0, '2026-01-31 12:00:00');
        KernelRuntimeHarness::seedFinancialLedgerEntry(self::$kernel, 'tenant-1', 'vendor-1', 1000.0, '2026-02-01 00:00:00');
        $uri = '/api/vendor/payout/statement/vendor-1?from=2026-01-01&to=2026-01-31&currency=USD';
        $responseA = KernelRuntimeHarness::requestJson(self::$kernel, 'GET', $uri, null, ['Authorization' => 'Bearer '.self::$financeToken]);
        $responseB = KernelRuntimeHarness::requestJson(self::$kernel, 'GET', $uri, null, ['Authorization' => 'Bearer '.self::$otherTenantToken]);
        self::assertSame(200, $responseA->getStatusCode());
        self::assertSame(200, $responseB->getStatusCode());
        $a = KernelRuntimeHarness::decodeJson($responseA)['data'];
        $b = KernelRuntimeHarness::decodeJson($responseB)['data'];
        self::assertIsArray($a);
        self::assertIsArray($b);
        self::assertSame('tenant-1', $a['tenantId']);
        self::assertSame('tenant-2', $b['tenantId']);
        self::assertEquals(125.0, $a['earnings']);
        self::assertEquals(500.0, $b['earnings']);
        $spoof = KernelRuntimeHarness::requestJson(self::$kernel, 'GET', $uri.'&tenantId=tenant-2', null, ['Authorization' => 'Bearer '.self::$financeToken]);
        self::assertSame(403, $spoof->getStatusCode());
    }

    public function testNumericLedgerIdentifierDoesNotSelectUnrelatedCanonicalOwnership(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite required');
        }
        $token = KernelRuntimeHarness::seedFinancialApiKey(self::$kernel, 'tenant-numeric', '987654321', 'read:finance');
        $response = KernelRuntimeHarness::requestJson(self::$kernel, 'GET', '/api/vendor/runtime/finance/987654321', null, ['Authorization' => 'Bearer '.$token]);
        self::assertSame(200, $response->getStatusCode());
        $data = KernelRuntimeHarness::decodeJson($response)['data'];
        self::assertIsArray($data);
        self::assertSame('987654321', $data['vendorId']);
        $ownership = $data['ownership'];
        self::assertIsArray($ownership);
        self::assertIsInt($ownership['vendorId']);
        self::assertNotSame(987654321, $ownership['vendorId']);
        $wrong = KernelRuntimeHarness::requestJson(self::$kernel, 'GET', '/api/vendor/runtime/finance/987654322', null, ['Authorization' => 'Bearer '.$token]);
        self::assertSame(403, $wrong->getStatusCode());
    }
}
