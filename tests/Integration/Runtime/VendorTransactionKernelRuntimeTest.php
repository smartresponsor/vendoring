<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Runtime;

use App\Vendoring\Tests\Support\Runtime\KernelRuntimeHarness;
use PHPUnit\Framework\TestCase;

final class VendorTransactionKernelRuntimeTest extends TestCase
{
    public function testKernelRuntimeCoversAuthenticatedCreateListAndStatusUpdateFlow(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));

        try {
            $token = KernelRuntimeHarness::seedActiveApiKey($kernel, 'write:transactions,read:transactions', $vendorId);
            $headers = ['Authorization' => 'Bearer '.$token];

            $createResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor/transaction', [
                'vendorId' => $vendorId,
                'orderId' => 'order-1',
                'projectId' => 'project-1',
                'amount' => '10.50',
            ], $headers);
            $createPayload = KernelRuntimeHarness::decodeJson($createResponse);

            self::assertSame(201, $createResponse->getStatusCode());
            self::assertSame('1', $createResponse->headers->get('X-API-Version'));
            self::assertSame('5', $createResponse->headers->get('X-RateLimit-Limit'));
            self::assertSame('4', $createResponse->headers->get('X-RateLimit-Remaining'));
            self::assertSame('pending', $createPayload['status']);
            self::assertIsInt($createPayload['id']);

            $listResponse = KernelRuntimeHarness::requestJson($kernel, 'GET', '/api/vendor/transaction/'.$vendorId, null, $headers);
            $listPayload = KernelRuntimeHarness::decodeJson($listResponse);

            self::assertSame(200, $listResponse->getStatusCode());
            self::assertSame('1', $listResponse->headers->get('X-API-Version'));
            self::assertFalse($listResponse->headers->has('X-RateLimit-Limit'));
            self::assertIsArray($listPayload['data'] ?? null);
            $listData = $listPayload['data'];
            self::assertCount(1, $listData);
            self::assertIsArray($listData[0] ?? null);
            $firstRow = $listData[0];
            self::assertSame($vendorId, $firstRow['vendorId'] ?? null);
            self::assertSame('order-1', $firstRow['orderId'] ?? null);
            self::assertSame('project-1', $firstRow['projectId'] ?? null);
            self::assertSame('10.50', $firstRow['amount'] ?? null);
            self::assertSame('pending', $firstRow['status'] ?? null);

            $updateResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor/transaction/status/'.$createPayload['id'], [
                'status' => 'authorized',
            ], $headers);
            $updatePayload = KernelRuntimeHarness::decodeJson($updateResponse);

            self::assertSame(200, $updateResponse->getStatusCode());
            self::assertSame($createPayload['id'], $updatePayload['id']);
            self::assertSame('authorized', $updatePayload['status']);

            $reloadedListResponse = KernelRuntimeHarness::requestJson($kernel, 'GET', '/api/vendor/transaction/'.$vendorId, null, $headers);
            $reloadedListPayload = KernelRuntimeHarness::decodeJson($reloadedListResponse);

            self::assertIsArray($reloadedListPayload['data'] ?? null);
            $reloadedData = $reloadedListPayload['data'];
            self::assertIsArray($reloadedData[0] ?? null);
            self::assertSame('authorized', $reloadedData[0]['status'] ?? null);

            $duplicateResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor/transaction', [
                'vendorId' => $vendorId,
                'orderId' => 'order-1',
                'projectId' => 'project-1',
                'amount' => '10.50',
            ], $headers);
            $duplicatePayload = KernelRuntimeHarness::decodeJson($duplicateResponse);

            self::assertSame(409, $duplicateResponse->getStatusCode());
            self::assertSame('duplicate_transaction', $duplicatePayload['error']);
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }

    public function testKernelRuntimeRejectsMissingAuthenticationOnWriteEndpoints(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));

        try {
            $createResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor/transaction', [
                'vendorId' => '1',
                'orderId' => 'order-1',
                'amount' => '10.50',
            ]);
            $createPayload = KernelRuntimeHarness::decodeJson($createResponse);

            self::assertSame(401, $createResponse->getStatusCode());
            self::assertSame('authentication_required', $createPayload['error']);
            self::assertSame('write:transactions', $createResponse->headers->get('X-Auth-Required-Permission'));
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }

    public function testKernelRuntimeFreshBootHandlesMalformedAndMissingPayloadsForAuthenticatedWrites(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));

        try {
            $token = KernelRuntimeHarness::seedActiveApiKey($kernel, 'write:transactions,read:transactions', $vendorId);
            $headers = ['Authorization' => 'Bearer '.$token];

            $malformedResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor/transaction', null, $headers);
            $malformedPayload = KernelRuntimeHarness::decodeJson($malformedResponse);

            self::assertSame(400, $malformedResponse->getStatusCode());
            self::assertSame('malformed_json', $malformedPayload['error']);

            $missingStatusResponse = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor/transaction/status/404', [], $headers);
            $missingStatusPayload = KernelRuntimeHarness::decodeJson($missingStatusResponse);

            self::assertSame(404, $missingStatusResponse->getStatusCode());
            self::assertSame('not_found', $missingStatusPayload['error']);
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }

    public function testCrossVendorWritesAndReadsAreDeniedWithoutMutation(): void
    {
        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));
        try {
            $a = KernelRuntimeHarness::seedActiveApiKey($kernel, 'write:transactions,read:transactions', $vendorA);
            $b = KernelRuntimeHarness::seedActiveApiKey($kernel, 'write:transactions,read:transactions', $vendorB);
            $headersA = ['Authorization' => 'Bearer '.$a];
            $headersB = ['Authorization' => 'Bearer '.$b];
            $legacyId = KernelRuntimeHarness::seedUnownedTransaction($kernel, (string) $vendorB);
            self::assertSame(404, KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor/transaction/status/'.$legacyId, ['status' => 'authorized'], $headersB)->getStatusCode());
            $input = ['vendorId' => $vendorB, 'orderId' => 'owned-order', 'amount' => '10.50'];
            self::assertSame(403, KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor/transaction', $input, $headersA)->getStatusCode());
            $created = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor/transaction', $input, $headersB);
            self::assertSame(201, $created->getStatusCode());
            $id = KernelRuntimeHarness::decodeJson($created)['id'];
            self::assertIsInt($id);
            $path = '/api/vendor/transaction/status/'.$id;
            self::assertSame(401, KernelRuntimeHarness::requestJson($kernel, 'POST', $path, ['status' => 'authorized'])->getStatusCode());
            self::assertSame(404, KernelRuntimeHarness::requestJson($kernel, 'POST', $path, ['status' => 'authorized'], $headersA)->getStatusCode());
            self::assertSame(403, KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor/transaction/status/'.$vendorB.'/'.$id, ['status' => 'authorized'], $headersA)->getStatusCode());
            self::assertSame(403, KernelRuntimeHarness::requestJson($kernel, 'GET', '/api/vendor/transaction/'.$vendorB, null, $headersA)->getStatusCode());
            $list = KernelRuntimeHarness::requestJson($kernel, 'GET', '/api/vendor/transaction/'.$vendorB, null, $headersB);
            self::assertSame(200, $list->getStatusCode());
            $rows = KernelRuntimeHarness::decodeJson($list)['data'];
            self::assertIsArray($rows);
            self::assertCount(1, $rows);
            self::assertIsArray($rows[0]);
            self::assertSame('pending', $rows[0]['status']);
            $mobile = '/api/vendor/transaction/list/'.$vendorB;
            self::assertSame(401, KernelRuntimeHarness::requestJson($kernel, 'GET', $mobile)->getStatusCode());
            self::assertSame(403, KernelRuntimeHarness::requestJson($kernel, 'GET', $mobile, null, $headersA)->getStatusCode());
            $mobileResponse = KernelRuntimeHarness::requestJson($kernel, 'GET', $mobile, null, $headersB);
            self::assertSame(200, $mobileResponse->getStatusCode());
            self::assertSame($rows, KernelRuntimeHarness::decodeJson($mobileResponse)['data']);
            self::assertSame(200, KernelRuntimeHarness::requestJson($kernel, 'POST', $path, ['status' => 'authorized'], $headersB)->getStatusCode());
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }

    public function testKernelRuntimeEnforcesConfiguredWriteRateLimit(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));
        try {
            $token = KernelRuntimeHarness::seedActiveApiKey($kernel, 'write:transactions,read:transactions', $vendorId);
            $headers = ['Authorization' => 'Bearer '.$token];
            for ($attempt = 0; $attempt < 5; ++$attempt) {
                $response = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor/transaction', [
                    'vendorId' => $vendorId,
                    'orderId' => 'rate-order-'.$attempt,
                    'amount' => '10.50',
                ], $headers);
                self::assertSame(201, $response->getStatusCode());
            }
            $response = KernelRuntimeHarness::requestJson($kernel, 'POST', '/api/vendor/transaction', [
                'vendorId' => $vendorId,
                'orderId' => 'rate-order-denied',
                'amount' => '10.50',
            ], $headers);
            self::assertSame(429, $response->getStatusCode());
            self::assertSame('5', $response->headers->get('X-RateLimit-Limit'));
            self::assertSame('0', $response->headers->get('X-RateLimit-Remaining'));
            self::assertSame('rate_limit_exceeded', KernelRuntimeHarness::decodeJson($response)['error']);
            self::assertGreaterThan(0, (int) $response->headers->get('Retry-After'));
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }

    public function testKernelRuntimePropagatesCorrelationHeaderForAuthenticatedCreate(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for kernel runtime integration test');
        }

        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));

        try {
            $token = KernelRuntimeHarness::seedActiveApiKey($kernel, 'write:transactions,read:transactions', $vendorId);
            $response = KernelRuntimeHarness::requestJson(
                $kernel,
                'POST',
                '/api/vendor/transaction',
                [
                    'vendorId' => $vendorId,
                    'orderId' => 'order-2',
                    'projectId' => null,
                    'amount' => '20.00',
                ],
                [
                    'X-Correlation-ID' => 'corr-runtime-001',
                    'Authorization' => 'Bearer '.$token,
                ],
            );

            self::assertSame(201, $response->getStatusCode());
            self::assertSame('corr-runtime-001', $response->headers->get('X-Correlation-ID'));
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }
}
