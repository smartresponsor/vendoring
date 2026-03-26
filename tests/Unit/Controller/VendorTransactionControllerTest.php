<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\VendorTransactionController;
use App\Entity\Vendor\VendorTransaction;
use App\Service\VendorTransactionInputResolver;
use App\Tests\Support\Transaction\FakeVendorTransactionManager;
use App\Tests\Support\Transaction\InMemoryVendorTransactionRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorTransactionControllerTest extends TestCase
{
    public function testCreateNormalizesBlankProjectIdBeforePassingToManager(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $manager = new FakeVendorTransactionManager($transaction);
        $controller = new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            $manager,
            new VendorTransactionInputResolver(),
        );

        $response = $controller->create(Request::create('/', 'POST', content: json_encode([
            'vendorId' => ' vendor-1 ',
            'orderId' => ' order-1 ',
            'projectId' => '   ',
            'amount' => '10.00',
        ], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(201, $response->getStatusCode());
        self::assertSame(42, $payload['id']);
        self::assertNotNull($manager->createdData);
        self::assertSame('vendor-1', $manager->createdData->vendorId);
        self::assertSame('order-1', $manager->createdData->orderId);
        self::assertNull($manager->createdData->projectId);
    }

    public function testCreateReturnsConflictForDuplicateTransaction(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $manager = new FakeVendorTransactionManager($transaction);
        $manager->exceptionToThrow = new \InvalidArgumentException('duplicate_transaction');

        $controller = new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            $manager,
            new VendorTransactionInputResolver(),
        );

        $response = $controller->create(Request::create('/', 'POST', content: json_encode([
            'vendorId' => 'vendor-1',
            'orderId' => 'order-1',
            'amount' => '10.00',
        ], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(409, $response->getStatusCode());
        self::assertSame('duplicate_transaction', $payload['error']);
    }

    public function testCreateReturnsValidationErrorForInvalidAmount(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            new FakeVendorTransactionManager($transaction),
            new VendorTransactionInputResolver(),
        );

        $response = $controller->create(Request::create('/', 'POST', content: json_encode([
            'vendorId' => 'vendor-1',
            'orderId' => 'order-1',
            'amount' => '',
        ], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('amount_required', $payload['error']);
    }

    public function testCreateReturnsValidationErrorForBlankVendorIdAfterTrim(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            new FakeVendorTransactionManager($transaction),
            new VendorTransactionInputResolver(),
        );

        $response = $controller->create(Request::create('/', 'POST', content: json_encode([
            'vendorId' => '   ',
            'orderId' => 'order-1',
            'amount' => '10.00',
        ], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('vendor_id_required', $payload['error']);
    }

    public function testCreateReturnsValidationErrorForBlankOrderIdAfterTrim(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            new FakeVendorTransactionManager($transaction),
            new VendorTransactionInputResolver(),
        );

        $response = $controller->create(Request::create('/', 'POST', content: json_encode([
            'vendorId' => 'vendor-1',
            'orderId' => '   ',
            'amount' => '10.00',
        ], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('order_id_required', $payload['error']);
    }

    public function testCreateReturnsBadRequestForMalformedJson(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            new FakeVendorTransactionManager($transaction),
            new VendorTransactionInputResolver(),
        );

        $response = $controller->create(Request::create('/', 'POST', content: '{invalid-json'));
        $payload = self::decodePayload($response);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('malformed_json', $payload['error']);
    }

    public function testUpdateStatusUsesRepositoryLookupByVendorAndId(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $manager = new FakeVendorTransactionManager($transaction);
        $controller = new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            $manager,
            new VendorTransactionInputResolver(),
        );

        $request = Request::create('/', 'POST', content: json_encode(['status' => 'settled'], JSON_THROW_ON_ERROR));
        $response = $controller->updateStatus('vendor-1', 42, $request);
        $payload = self::decodePayload($response);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('settled', $payload['status']);
        self::assertSame('settled', $manager->updatedStatus);
        self::assertSame($transaction, $manager->updatedTransaction);
    }

    public function testUpdateStatusReturnsNotFoundWhenVendorDoesNotOwnTransaction(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            new FakeVendorTransactionManager($transaction),
            new VendorTransactionInputResolver(),
        );

        $response = $controller->updateStatus('vendor-2', 42, Request::create('/', 'POST', content: json_encode(['status' => 'settled'], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('not_found', $payload['error']);
    }

    public function testUpdateStatusReturnsBadRequestForMalformedJson(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            new FakeVendorTransactionManager($transaction),
            new VendorTransactionInputResolver(),
        );

        $response = $controller->updateStatus('vendor-1', 42, Request::create('/', 'POST', content: '{invalid-json'));
        $payload = self::decodePayload($response);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('malformed_json', $payload['error']);
    }

    public function testUpdateStatusReturnsValidationErrorWhenStatusMissing(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            new FakeVendorTransactionManager($transaction),
            new VendorTransactionInputResolver(),
        );

        $response = $controller->updateStatus('vendor-1', 42, Request::create('/', 'POST', content: json_encode([], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('status_required', $payload['error']);
    }

    public function testUpdateStatusReturnsValidationErrorForInvalidTransition(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $manager = new FakeVendorTransactionManager($transaction);
        $manager->exceptionToThrow = new \InvalidArgumentException('invalid_status_transition');

        $controller = new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            $manager,
            new VendorTransactionInputResolver(),
        );

        $response = $controller->updateStatus('vendor-1', 42, Request::create('/', 'POST', content: json_encode(['status' => 'refunded'], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('invalid_status_transition', $payload['error']);
    }

    public function testCreateMapsUnknownValidationMessageToStableErrorCode(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $manager = new FakeVendorTransactionManager($transaction);
        $manager->exceptionToThrow = new \InvalidArgumentException('low_level_sql_error');

        $controller = new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            $manager,
            new VendorTransactionInputResolver(),
        );

        $response = $controller->create(Request::create('/', 'POST', content: json_encode([
            'vendorId' => 'vendor-1',
            'orderId' => 'order-1',
            'amount' => '10.00',
        ], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('transaction_validation_error', $payload['error']);
    }

    /** @return array<string, mixed> */
    private static function decodePayload(JsonResponse $response): array
    {
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($payload)) {
            self::fail('Expected array payload.');
        }

        return $payload;
    }

    private function forceId(VendorTransaction $transaction, int $id): void
    {
        $reflection = new \ReflectionObject($transaction);
        $property = $reflection->getProperty('id');
        $property->setValue($transaction, $id);
    }
}
