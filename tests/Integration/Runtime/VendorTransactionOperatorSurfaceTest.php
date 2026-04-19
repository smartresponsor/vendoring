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
            $indexResponse = KernelRuntimeHarness::requestForm($kernel, 'GET', '/ops/vendor-transactions/vendor-ops');

            self::assertSame(200, $indexResponse->getStatusCode());
            self::assertStringContainsString('Vendor transaction operator surface', (string) $indexResponse->getContent());
            self::assertStringContainsString('Create transaction', (string) $indexResponse->getContent());
            self::assertStringContainsString('No transactions found for this vendor yet.', (string) $indexResponse->getContent());

            $createResponse = KernelRuntimeHarness::requestForm($kernel, 'POST', '/ops/vendor-transactions/vendor-ops/create', [
                'orderId' => 'order-ops-1',
                'projectId' => 'project-ops-1',
                'amount' => '15.75',
            ]);

            self::assertSame(302, $createResponse->getStatusCode());
            self::assertSame('/ops/vendor-transactions/vendor-ops?message=Transaction%20created.', $createResponse->headers->get('Location'));

            $reloadedResponse = KernelRuntimeHarness::requestForm($kernel, 'GET', '/ops/vendor-transactions/vendor-ops?message=Transaction%20created.');
            $reloadedHtml = (string) $reloadedResponse->getContent();

            self::assertStringContainsString('Transaction created.', $reloadedHtml);
            self::assertStringContainsString('order-ops-1', $reloadedHtml);
            self::assertStringContainsString('project-ops-1', $reloadedHtml);
            self::assertStringContainsString('15.75', $reloadedHtml);
            self::assertStringContainsString('pending', $reloadedHtml);

            $statusResponse = KernelRuntimeHarness::requestForm($kernel, 'POST', '/ops/vendor-transactions/vendor-ops/1/status', [
                'status' => 'authorized',
            ]);

            self::assertSame(302, $statusResponse->getStatusCode());
            self::assertSame('/ops/vendor-transactions/vendor-ops?message=Transaction%20status%20updated.', $statusResponse->headers->get('Location'));

            $authorizedResponse = KernelRuntimeHarness::requestForm($kernel, 'GET', '/ops/vendor-transactions/vendor-ops?message=Transaction%20status%20updated.');

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
            $createResponse = KernelRuntimeHarness::requestForm($kernel, 'POST', '/ops/vendor-transactions/vendor-ops/create', [
                'orderId' => '',
                'amount' => '7.00',
            ]);

            self::assertSame(302, $createResponse->getStatusCode());
            self::assertSame('/ops/vendor-transactions/vendor-ops?error=order_id_required', $createResponse->headers->get('Location'));

            $errorResponse = KernelRuntimeHarness::requestForm($kernel, 'GET', '/ops/vendor-transactions/vendor-ops?error=order_id_required');

            self::assertStringContainsString('order_id_required', (string) $errorResponse->getContent());
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }
}
