<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Integration\Runtime;

use App\Tests\Support\Runtime\KernelRuntimeHarness;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ApiQueryValidationKernelRuntimeTest extends TestCase
{
    #[DataProvider('validationSurfaceProvider')]
    public function testKernelRuntimeReturnsExpectedApiValidationErrorCodes(string $uri, string $expectedError): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));

        try {
            $response = KernelRuntimeHarness::requestJson($kernel, 'GET', $uri);
            $payload = KernelRuntimeHarness::decodeJson($response);

            self::assertSame(422, $response->getStatusCode());
            self::assertSame($expectedError, $payload['error'] ?? null);
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }

    /**
     * @return iterable<string, array{uri: string, expectedError: string}>
     */
    public static function validationSurfaceProvider(): iterable
    {
        yield 'finance runtime requires tenantId' => [
            'uri' => '/api/vendor/runtime/vendor-1/finance',
            'expectedError' => 'tenant_id_required',
        ];

        yield 'statement runtime requires from' => [
            'uri' => '/api/payouts/statements/vendor-1?tenantId=tenant-1&to=2026-01-31&currency=USD',
            'expectedError' => 'statement_from_required',
        ];

        yield 'statement runtime requires to' => [
            'uri' => '/api/payouts/statements/vendor-1?tenantId=tenant-1&from=2026-01-01&currency=USD',
            'expectedError' => 'statement_to_required',
        ];
    }

    public function testKernelRuntimeReturnsDedicatedErrorForOutOfRangePayoutRetentionFeePercent(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));

        try {
            $response = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/payout/create', [
                'tenantId' => 'tenant-1',
                'vendorId' => 'vendor-1',
                'currency' => 'USD',
                'thresholdCents' => 1000,
                'retentionFeePercent' => 1.5,
            ]);
            $payload = KernelRuntimeHarness::decodeJson($response);

            self::assertSame(422, $response->getStatusCode());
            self::assertSame('retention_fee_percent_out_of_range', $payload['error'] ?? null);
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }
}
