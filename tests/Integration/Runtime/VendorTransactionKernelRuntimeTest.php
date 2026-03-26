<?php

declare(strict_types=1);

namespace App\Tests\Integration\Runtime;

use App\Tests\Support\Runtime\KernelRuntimeHarness;
use PHPUnit\Framework\TestCase;

final class VendorTransactionKernelRuntimeTest extends TestCase
{
    public function testKernelRuntimeCoversCreateListAndStatusUpdateFlow(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));

        try {
            $createResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor-transactions', [
                'vendorId' => 'vendor-1',
                'orderId' => 'order-1',
                'projectId' => 'project-1',
                'amount' => '10.50',
            ]);
            $createPayload = KernelRuntimeHarness::decodeJson($createResponse);

            self::assertSame(201, $createResponse->getStatusCode());
            self::assertSame('pending', $createPayload['status']);
            self::assertIsInt($createPayload['id']);

            $listResponse = KernelRuntimeHarness::requestJson($kernel, 'GET', '/api/vendor-transactions/vendor/vendor-1');
            $listPayload = KernelRuntimeHarness::decodeJson($listResponse);

            self::assertSame(200, $listResponse->getStatusCode());
            self::assertIsArray($listPayload['data'] ?? null);
            $listData = $listPayload['data'];
            self::assertCount(1, $listData);
            self::assertIsArray($listData[0] ?? null);
            $firstRow = $listData[0];
            self::assertSame('vendor-1', $firstRow['vendorId'] ?? null);
            self::assertSame('order-1', $firstRow['orderId'] ?? null);
            self::assertSame('project-1', $firstRow['projectId'] ?? null);
            self::assertSame('10.50', $firstRow['amount'] ?? null);
            self::assertSame('pending', $firstRow['status'] ?? null);

            $updateResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor-transactions/vendor/vendor-1/'.$createPayload['id'].'/status', [
                'status' => 'authorized',
            ]);
            $updatePayload = KernelRuntimeHarness::decodeJson($updateResponse);

            self::assertSame(200, $updateResponse->getStatusCode());
            self::assertSame($createPayload['id'], $updatePayload['id']);
            self::assertSame('authorized', $updatePayload['status']);

            $reloadedListResponse = KernelRuntimeHarness::requestJson($kernel, 'GET', '/api/vendor-transactions/vendor/vendor-1');
            $reloadedListPayload = KernelRuntimeHarness::decodeJson($reloadedListResponse);

            self::assertIsArray($reloadedListPayload['data'] ?? null);
            $reloadedData = $reloadedListPayload['data'];
            self::assertIsArray($reloadedData[0] ?? null);
            self::assertSame('authorized', $reloadedData[0]['status'] ?? null);

            $duplicateResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor-transactions', [
                'vendorId' => 'vendor-1',
                'orderId' => 'order-1',
                'projectId' => 'project-1',
                'amount' => '10.50',
            ]);
            $duplicatePayload = KernelRuntimeHarness::decodeJson($duplicateResponse);

            self::assertSame(409, $duplicateResponse->getStatusCode());
            self::assertSame('duplicate_transaction', $duplicatePayload['error']);
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }

    public function testKernelRuntimeFreshBootHandlesMalformedAndMissingPayloads(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));

        try {
            $malformedResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor-transactions', null);
            $malformedPayload = KernelRuntimeHarness::decodeJson($malformedResponse);

            self::assertSame(400, $malformedResponse->getStatusCode());
            self::assertSame('malformed_json', $malformedPayload['error']);

            $missingStatusResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor-transactions/vendor/vendor-1/404/status', []);
            $missingStatusPayload = KernelRuntimeHarness::decodeJson($missingStatusResponse);

            self::assertSame(404, $missingStatusResponse->getStatusCode());
            self::assertSame('not_found', $missingStatusPayload['error']);
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }
}
