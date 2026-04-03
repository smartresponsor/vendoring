<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\VendorTransactionController;
use App\Entity\Vendor;
use App\Entity\VendorTransaction;
use App\Observability\Service\CorrelationContext;
use App\Observability\Service\RuntimeLogger;
use App\Service\Traffic\FileWriteRateLimiter;
use App\Service\VendorTransactionInputResolverService;
use App\ServiceInterface\VendorApiKeyServiceInterface;
use App\Tests\Support\Transaction\FakeVendorTransactionManager;
use App\Tests\Support\Transaction\InMemoryVendorTransactionRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class VendorTransactionControllerTest extends TestCase
{
    public function testCreateRejectsMissingAuthenticationHeader(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = $this->controller($transaction, apiKeyService: $this->createMock(VendorApiKeyServiceInterface::class));

        $response = $controller->create(Request::create('/', 'POST', content: json_encode([
            'vendorId' => 'vendor-1',
            'orderId' => 'order-1',
            'amount' => '10.00',
        ], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('authentication_required', $payload['error']);
        self::assertSame('write:transactions', $payload['requiredPermission']);
        self::assertSame('Bearer', $response->headers->get('WWW-Authenticate'));
    }

    public function testCreateRejectsInvalidAuthenticationHeader(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = $this->controller($transaction, apiKeyService: $this->invalidApiKeyService());

        $response = $controller->create($this->authorizedJsonRequest([
            'vendorId' => 'vendor-1',
            'orderId' => 'order-1',
            'amount' => '10.00',
        ]));
        $payload = self::decodePayload($response);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('invalid_api_token', $payload['error']);
        self::assertSame('write:transactions', $response->headers->get('X-Auth-Required-Permission'));
    }

    public function testCreateRejectsUnderScopedToken(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = $this->controller($transaction, apiKeyService: $this->underScopedApiKeyService());

        $response = $controller->create($this->authorizedJsonRequest([
            'vendorId' => 'vendor-1',
            'orderId' => 'order-1',
            'amount' => '10.00',
        ]));
        $payload = self::decodePayload($response);

        self::assertSame(403, $response->getStatusCode());
        self::assertSame('permission_denied', $payload['error']);
        self::assertSame('write:transactions', $payload['requiredPermission']);
    }

    public function testCreateNormalizesBlankProjectIdBeforePassingToManager(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $manager = new FakeVendorTransactionManager($transaction);
        $controller = $this->controller($transaction, $manager);

        $response = $controller->create($this->authorizedJsonRequest([
            'vendorId' => ' vendor-1 ',
            'orderId' => ' order-1 ',
            'projectId' => '   ',
            'amount' => '10.00',
        ]));
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
        $controller = $this->controller($transaction, $manager);

        $response = $controller->create($this->authorizedJsonRequest([
            'vendorId' => 'vendor-1',
            'orderId' => 'order-1',
            'amount' => '10.00',
        ]));
        $payload = self::decodePayload($response);

        self::assertSame(409, $response->getStatusCode());
        self::assertSame('duplicate_transaction', $payload['error']);
    }

    public function testCreateReturnsValidationErrorForInvalidAmount(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = $this->controller($transaction);

        $response = $controller->create($this->authorizedJsonRequest([
            'vendorId' => 'vendor-1',
            'orderId' => 'order-1',
            'amount' => '',
        ]));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('amount_required', $payload['error']);
    }

    public function testCreateReturnsValidationErrorForBlankVendorIdAfterTrim(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = $this->controller($transaction);

        $response = $controller->create($this->authorizedJsonRequest([
            'vendorId' => '   ',
            'orderId' => 'order-1',
            'amount' => '10.00',
        ]));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('vendor_id_required', $payload['error']);
    }

    public function testCreateReturnsValidationErrorForBlankOrderIdAfterTrim(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = $this->controller($transaction);

        $response = $controller->create($this->authorizedJsonRequest([
            'vendorId' => 'vendor-1',
            'orderId' => '   ',
            'amount' => '10.00',
        ]));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('order_id_required', $payload['error']);
    }

    public function testCreateReturnsBadRequestForMalformedJson(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = $this->controller($transaction);

        $response = $controller->create($this->authorizedJsonRequest(content: '{invalid-json'));
        $payload = self::decodePayload($response);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('malformed_json', $payload['error']);
    }

    public function testUpdateStatusUsesRepositoryLookupByVendorAndId(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $manager = new FakeVendorTransactionManager($transaction);
        $controller = $this->controller($transaction, $manager);

        $response = $controller->updateStatus('vendor-1', 42, $this->authorizedJsonRequest([
            'status' => 'settled',
        ]));
        $payload = self::decodePayload($response);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('settled', $payload['status']);
        self::assertSame('settled', $manager->updatedStatus);
        self::assertSame($transaction, $manager->updatedTransaction);
    }

    public function testUpdateStatusRejectsUnderScopedToken(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = $this->controller($transaction, apiKeyService: $this->underScopedApiKeyService());

        $response = $controller->updateStatus('vendor-1', 42, $this->authorizedJsonRequest([
            'status' => 'settled',
        ]));
        $payload = self::decodePayload($response);

        self::assertSame(403, $response->getStatusCode());
        self::assertSame('permission_denied', $payload['error']);
    }

    public function testUpdateStatusReturnsNotFoundWhenVendorDoesNotOwnTransaction(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = $this->controller($transaction);

        $response = $controller->updateStatus('vendor-2', 42, $this->authorizedJsonRequest([
            'status' => 'settled',
        ]));
        $payload = self::decodePayload($response);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('not_found', $payload['error']);
    }

    public function testUpdateStatusReturnsBadRequestForMalformedJson(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = $this->controller($transaction);

        $response = $controller->updateStatus('vendor-1', 42, $this->authorizedJsonRequest(content: '{invalid-json'));
        $payload = self::decodePayload($response);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('malformed_json', $payload['error']);
    }

    public function testUpdateStatusReturnsValidationErrorWhenStatusMissing(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);

        $controller = $this->controller($transaction);

        $response = $controller->updateStatus('vendor-1', 42, $this->authorizedJsonRequest([]));
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
        $controller = $this->controller($transaction, $manager);

        $response = $controller->updateStatus('vendor-1', 42, $this->authorizedJsonRequest([
            'status' => 'refunded',
        ]));
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
        $controller = $this->controller($transaction, $manager);

        $response = $controller->create($this->authorizedJsonRequest([
            'vendorId' => 'vendor-1',
            'orderId' => 'order-1',
            'amount' => '10.00',
        ]));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('transaction_validation_error', $payload['error']);
    }

    public function testCreateReturnsTooManyRequestsWhenWriteRateLimitIsExceeded(): void
    {
        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $this->forceId($transaction, 42);
        $rateLimitKey = 'controller-rate-limit-test-'.bin2hex(random_bytes(6));

        $controller = $this->controller($transaction);

        for ($attempt = 0; $attempt < 5; ++$attempt) {
            $accepted = $controller->create($this->authorizedJsonRequest([
                'vendorId' => 'vendor-1',
                'orderId' => 'order-'.$attempt,
                'amount' => '10.00',
            ], ['HTTP_X_RATE_LIMIT_KEY' => $rateLimitKey]));

            self::assertSame(201, $accepted->getStatusCode());
        }

        $rejected = $controller->create($this->authorizedJsonRequest([
            'vendorId' => 'vendor-1',
            'orderId' => 'order-overflow',
            'amount' => '10.00',
        ], ['HTTP_X_RATE_LIMIT_KEY' => $rateLimitKey]));
        $payload = self::decodePayload($rejected);

        self::assertSame(429, $rejected->getStatusCode());
        self::assertSame('rate_limit_exceeded', $payload['error']);
        self::assertTrue($rejected->headers->has('Retry-After'));
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

    private function runtimeLogger(): RuntimeLogger
    {
        return new RuntimeLogger(new CorrelationContext(), new RequestStack());
    }

    private function controller(?VendorTransaction $transaction = null, ?FakeVendorTransactionManager $manager = null, ?VendorApiKeyServiceInterface $apiKeyService = null): VendorTransactionController
    {
        $transaction ??= new VendorTransaction('vendor-1', 'order-1', null, '10.00');
        $manager ??= new FakeVendorTransactionManager($transaction);

        return new VendorTransactionController(
            new InMemoryVendorTransactionRepository([$transaction]),
            $manager,
            new VendorTransactionInputResolverService(),
            $this->runtimeLogger(),
            new FileWriteRateLimiter(),
            $apiKeyService ?? $this->authorizedApiKeyService(),
        );
    }

    /** @return VendorApiKeyServiceInterface&MockObject */
    private function authorizedApiKeyService(): VendorApiKeyServiceInterface
    {
        $vendor = new Vendor('Vendor A');
        $service = $this->createMock(VendorApiKeyServiceInterface::class);
        $service->method('resolveVendorFromAuthHeader')->willReturn($vendor);
        $service->method('validateAuthorizationHeader')->with('Bearer valid-token', 'write:transactions')->willReturn($vendor);

        return $service;
    }

    /** @return VendorApiKeyServiceInterface&MockObject */
    private function invalidApiKeyService(): VendorApiKeyServiceInterface
    {
        $service = $this->createMock(VendorApiKeyServiceInterface::class);
        $service->method('resolveVendorFromAuthHeader')->with('Bearer valid-token')->willReturn(null);
        $service->expects(self::never())->method('validateAuthorizationHeader');

        return $service;
    }

    /** @return VendorApiKeyServiceInterface&MockObject */
    private function underScopedApiKeyService(): VendorApiKeyServiceInterface
    {
        $vendor = new Vendor('Vendor A');
        $service = $this->createMock(VendorApiKeyServiceInterface::class);
        $service->method('resolveVendorFromAuthHeader')->with('Bearer valid-token')->willReturn($vendor);
        $service->method('validateAuthorizationHeader')->with('Bearer valid-token', 'write:transactions')->willReturn(null);

        return $service;
    }

    /**
     * @param array<string, mixed>|null  $payload
     * @param array<string, string>      $server
     */
    private function authorizedJsonRequest(?array $payload = null, array $server = [], ?string $content = null): Request
    {
        $server = ['HTTP_AUTHORIZATION' => 'Bearer valid-token'] + $server;
        $content ??= null === $payload ? null : json_encode($payload, JSON_THROW_ON_ERROR);

        return Request::create('/', 'POST', server: $server, content: $content);
    }
}
