<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Functional\Panther;

final class VendoringRcPantherSmokeTest extends ExternalBasePantherTestCase
{
    public function testApiDocumentationSurfaceRendersVendoringApi(): void
    {
        $client = $this->createExternalBaseClient();
        $response = self::browserRequest($client, 'GET', '/api/doc', authenticated: false);
        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Vendoring API', $response['body']);
    }

    public function testVendorRuntimeStatusEndpointReturnsDataPayload(): void
    {
        $client = $this->createExternalBaseClient();
        $uri = '/api/vendor/runtime/status/42?tenantId=tenant-1&currency=USD';
        self::decodeJsonResponse(self::browserRequest($client, 'GET', $uri, authenticated: false), 403);
        $payload = self::decodeJsonResponse(self::browserRequest($client, 'GET', $uri));
        self::assertIsArray($payload['data'] ?? null);
        self::assertSame('tenant-1', $payload['data']['tenantId'] ?? null);
        self::assertSame('42', $payload['data']['vendorId'] ?? null);
        self::decodeJsonResponse(self::browserRequest($client, 'GET', str_replace('tenant-1', 'tenant-2', $uri)), 403);
    }

    public function testVendorReleaseBaselineEndpointReturnsDataPayload(): void
    {
        $client = $this->createExternalBaseClient();
        $uri = '/api/vendor/release/baseline/42?tenantId=tenant-1&currency=USD';
        $payload = self::decodeJsonResponse(self::browserRequest($client, 'GET', $uri));
        self::assertIsArray($payload['data'] ?? null);
        self::assertArrayHasKey('status', $payload['data']);
        self::assertSame('tenant-1', $payload['data']['tenantId'] ?? null);
    }

    public function testVendorTransactionCreateListAndStatusFlowWorksThroughExternalBase(): void
    {
        $client = $this->createExternalBaseClient();
        $vendorId = '42';
        $orderId = 'panther-order-'.bin2hex(random_bytes(6));
        $input = ['vendorId' => $vendorId, 'orderId' => $orderId, 'projectId' => 'panther-project', 'amount' => '15.00'];
        self::decodeJsonResponse(self::browserRequest($client, 'POST', '/api/vendor/transaction', $input, false), 401);
        $created = self::decodeJsonResponse(self::browserRequest($client, 'POST', '/api/vendor/transaction', $input), 201);
        self::assertIsInt($created['id'] ?? null);
        self::assertSame('pending', $created['status'] ?? null);
        $list = self::decodeJsonResponse(self::browserRequest($client, 'GET', '/api/vendor/transaction/'.$vendorId));
        self::assertIsArray($list['data'] ?? null);
        self::assertTrue(in_array($orderId, array_column($list['data'], 'orderId'), true));
        $updated = self::decodeJsonResponse(self::browserRequest($client, 'POST', '/api/vendor/transaction/status/'.$created['id'], ['status' => 'authorized']));
        self::assertSame($created['id'], $updated['id'] ?? null);
        self::assertSame('authorized', $updated['status'] ?? null);
    }

    public function testVendorTransactionDuplicatePayloadReturnsConflict(): void
    {
        $client = $this->createExternalBaseClient();
        $input = ['vendorId' => '42', 'orderId' => 'panther-dup-'.bin2hex(random_bytes(6)), 'projectId' => 'panther-project', 'amount' => '20.00'];
        self::decodeJsonResponse(self::browserRequest($client, 'POST', '/api/vendor/transaction', $input), 201);
        $duplicate = self::decodeJsonResponse(self::browserRequest($client, 'POST', '/api/vendor/transaction', $input), 409);
        self::assertSame('duplicate_transaction', $duplicate['error'] ?? null);
    }
}
