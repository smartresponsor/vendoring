<?php

declare(strict_types=1);

namespace App\Tests\Functional\Panther;

use Symfony\Component\Panther\Client;

final class VendoringRcPantherSmokeTest extends ExternalBasePantherTestCase
{
    public function testApiDocumentationSurfaceRendersVendoringApi(): void
    {
        $client = self::createExternalBaseClient();

        $client->request('GET', '/api/doc');

        self::assertSame(200, $client->getInternalResponse()->getStatusCode());
        self::assertStringContainsString('Vendoring API', $client->getPageSource());
    }

    public function testVendorRuntimeStatusEndpointReturnsDataPayload(): void
    {
        $client = self::createExternalBaseClient();

        $client->request('GET', '/api/vendor-runtime-status/tenant/tenant-1/vendor/42?currency=USD');

        $payload = self::decodeJsonResponse($client);

        self::assertArrayHasKey('data', $payload);
        self::assertIsArray($payload['data']);
        self::assertSame('tenant-1', $payload['data']['tenantId'] ?? null);
    }

    public function testVendorReleaseBaselineEndpointReturnsDataPayload(): void
    {
        $client = self::createExternalBaseClient();

        $client->request('GET', '/api/vendor-release-baseline/tenant/tenant-1/vendor/42?currency=USD');

        $payload = self::decodeJsonResponse($client);

        self::assertArrayHasKey('data', $payload);
        self::assertIsArray($payload['data']);
        self::assertArrayHasKey('status', $payload['data']);
    }

    public function testVendorTransactionCreateListAndStatusFlowWorksThroughExternalBase(): void
    {
        $client = self::createExternalBaseClient();

        $suffix = bin2hex(random_bytes(6));
        $vendorId = 'panther-vendor-' . $suffix;
        $orderId = 'panther-order-' . $suffix;

        // create
        $client->request(
            'POST',
            '/api/vendor-transactions',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'vendorId' => $vendorId,
                'orderId' => $orderId,
                'projectId' => 'panther-project',
                'amount' => '15.00',
            ], JSON_THROW_ON_ERROR),
        );

        $createPayload = self::decodeJsonResponse($client, 201);

        self::assertArrayHasKey('id', $createPayload);
        self::assertSame('pending', $createPayload['status']);

        $id = $createPayload['id'];

        // list
        $client->request('GET', '/api/vendor-transactions/vendor/' . $vendorId);
        $listPayload = self::decodeJsonResponse($client);

        self::assertIsArray($listPayload['data']);
        $found = false;

        foreach ($listPayload['data'] as $row) {
            if (!is_array($row)) {
                continue;
            }

            if (($row['orderId'] ?? null) === $orderId) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found, 'Created transaction not found in vendor list');

        // update status
        $transactionId = is_scalar($id) ? (string) $id : '';
        self::assertNotSame('', $transactionId);

        $client->request(
            'POST',
            '/api/vendor-transactions/vendor/' . $vendorId . '/' . $transactionId . '/status',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['status' => 'authorized'], JSON_THROW_ON_ERROR),
        );

        $updatePayload = self::decodeJsonResponse($client);

        self::assertSame($id, $updatePayload['id']);
        self::assertSame('authorized', is_string($updatePayload['status'] ?? null) ? $updatePayload['status'] : null);
    }

    public function testVendorTransactionDuplicatePayloadReturnsConflict(): void
    {
        $client = self::createExternalBaseClient();

        $suffix = bin2hex(random_bytes(6));
        $vendorId = 'panther-dup-' . $suffix;
        $orderId = 'panther-dup-order-' . $suffix;

        $payload = json_encode([
            'vendorId' => $vendorId,
            'orderId' => $orderId,
            'projectId' => 'panther-project',
            'amount' => '20.00',
        ], JSON_THROW_ON_ERROR);

        // first create
        $client->request('POST', '/api/vendor-transactions', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        self::decodeJsonResponse($client, 201);

        // duplicate
        $client->request('POST', '/api/vendor-transactions', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $duplicatePayload = self::decodeJsonResponse($client, 409);

        self::assertSame('duplicate_transaction', $duplicatePayload['error'] ?? null);
    }
}
