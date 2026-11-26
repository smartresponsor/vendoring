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
        $vendorId = self::$transactionVendorId;
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

    public function testAuthenticatedOperatorFormThroughRealBrowser(): void
    {
        $client = $this->createExternalBaseClient();
        $vendorId = self::$transactionVendorId;
        $url = '/ops/vendor/transaction/'.$vendorId;
        self::assertSame(403, self::browserRequest($client, 'GET', $url, authenticated: false)['status']);
        $page = self::browserRequest($client, 'GET', $url);
        self::assertSame(200, $page['status']);
        self::assertStringContainsString('Vendor transaction operator surface', $page['body']);
        self::assertStringContainsString('/ops/vendor/transaction/create/'.$vendorId, $page['body']);
        $orderId = 'panther-operator-'.bin2hex(random_bytes(6));
        $created = self::browserRequest($client, 'POST', '/ops/vendor/transaction/create/'.$vendorId, [
            'orderId' => $orderId,
            'amount' => '11.25',
        ], form: true);
        self::assertSame(200, $created['status']);
        self::assertStringContainsString($orderId, $created['body']);
        self::assertStringContainsString('Transaction created.', $created['body']);
        $foreign = self::browserRequest($client, 'POST', '/ops/vendor/transaction/create/999999', [
            'orderId' => 'foreign-order',
            'amount' => '11.25',
        ], form: true);
        self::assertSame(403, $foreign['status']);
    }

    public function testVendorTransactionDuplicatePayloadReturnsConflict(): void
    {
        $client = $this->createExternalBaseClient();
        $input = ['vendorId' => self::$transactionVendorId, 'orderId' => 'panther-dup-'.bin2hex(random_bytes(6)), 'projectId' => 'panther-project', 'amount' => '20.00'];
        self::decodeJsonResponse(self::browserRequest($client, 'POST', '/api/vendor/transaction', $input), 201);
        $duplicate = self::decodeJsonResponse(self::browserRequest($client, 'POST', '/api/vendor/transaction', $input), 409);
        self::assertSame('duplicate_transaction', $duplicate['error'] ?? null);
    }
}
