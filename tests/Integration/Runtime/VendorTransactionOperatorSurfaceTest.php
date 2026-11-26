<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Runtime;

use App\Vendoring\Tests\Support\Runtime\KernelRuntimeHarness;
use PHPUnit\Framework\TestCase;

final class VendorTransactionOperatorSurfaceTest extends TestCase
{
    public function testOperatorSurfaceRendersCreateAndStatusFlows(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for operator surface integration test');
        }

        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));

        try {
            $token = KernelRuntimeHarness::seedActiveApiKey($kernel, 'write:transactions,read:transactions', $vendorId);
            $headers = ['Authorization' => 'Bearer '.$token];
            self::assertSame(403, KernelRuntimeHarness::requestForm($kernel, 'POST', '/ops/vendor/transaction/create/'.$vendorId, ['orderId' => 'unauthorized', 'amount' => '10'])->getStatusCode());
            $indexResponse = KernelRuntimeHarness::requestForm($kernel, 'GET', '/ops/vendor/transaction/'.$vendorId, [], $headers);

            self::assertSame(200, $indexResponse->getStatusCode());
            self::assertStringContainsString('Vendor transaction operator surface', (string) $indexResponse->getContent());
            self::assertStringContainsString('Create transaction', (string) $indexResponse->getContent());
            self::assertStringContainsString('No transactions found for this vendor yet.', (string) $indexResponse->getContent());

            $createResponse = KernelRuntimeHarness::requestForm($kernel, 'POST', '/ops/vendor/transaction/create/'.$vendorId, [
                'orderId' => 'order-ops-1',
                'projectId' => 'project-ops-1',
                'amount' => '15.75',
            ], $headers);

            self::assertSame(302, $createResponse->getStatusCode());
            self::assertSame('/ops/vendor/transaction/'.$vendorId.'?message=Transaction%20created.', $createResponse->headers->get('Location'));

            $reloadedResponse = KernelRuntimeHarness::requestForm($kernel, 'GET', '/ops/vendor/transaction/'.$vendorId.'?message=Transaction%20created.', [], $headers);
            $reloadedHtml = (string) $reloadedResponse->getContent();

            self::assertStringContainsString('Transaction created.', $reloadedHtml);
            self::assertStringContainsString('order-ops-1', $reloadedHtml);
            self::assertStringContainsString('project-ops-1', $reloadedHtml);
            self::assertStringContainsString('15.75', $reloadedHtml);
            self::assertStringContainsString('pending', $reloadedHtml);

            $statusResponse = KernelRuntimeHarness::requestForm($kernel, 'POST', '/ops/vendor/transaction/status/1', [
                'status' => 'authorized',
            ], $headers);

            self::assertSame(302, $statusResponse->getStatusCode());
            self::assertSame('/ops/vendor/transaction/'.$vendorId.'?message=Transaction%20status%20updated.', $statusResponse->headers->get('Location'));

            $authorizedResponse = KernelRuntimeHarness::requestForm($kernel, 'GET', '/ops/vendor/transaction/'.$vendorId.'?message=Transaction%20status%20updated.', [], $headers);

            self::assertStringContainsString('Transaction status updated.', (string) $authorizedResponse->getContent());
            self::assertStringContainsString('authorized', (string) $authorizedResponse->getContent());
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }

    public function testOperatorSurfaceRendersValidationErrorFeedback(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for operator surface integration test');
        }

        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));

        try {
            $token = KernelRuntimeHarness::seedActiveApiKey($kernel, 'write:transactions,read:transactions', $vendorId);
            $headers = ['Authorization' => 'Bearer '.$token];
            $createResponse = KernelRuntimeHarness::requestForm($kernel, 'POST', '/ops/vendor/transaction/create/'.$vendorId, [
                'orderId' => '',
                'amount' => '7.00',
            ], $headers);

            self::assertSame(302, $createResponse->getStatusCode());
            self::assertSame('/ops/vendor/transaction/'.$vendorId.'?error=order_id_required', $createResponse->headers->get('Location'));

            $errorResponse = KernelRuntimeHarness::requestForm($kernel, 'GET', '/ops/vendor/transaction/'.$vendorId.'?error=order_id_required', [], $headers);

            self::assertStringContainsString('order_id_required', (string) $errorResponse->getContent());
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }
}
