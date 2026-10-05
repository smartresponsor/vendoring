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

    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            return;
        }

        self::$kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));
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
        ]);
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
        ]);
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
        ]);
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
        ]);
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
        ]);
        $payload = KernelRuntimeHarness::decodeJson($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('threshold_cents_required', $payload['errorCode'] ?? null);
    }
}
