<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Controller\Payout;

use App\Vendoring\Controller\Vendor\VendorPayoutController;
use App\Vendoring\DTO\Payout\VendorCreatePayoutDTO;
use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\Entity\Vendor\VendorPayoutItemEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorPayoutRepositoryInterface;
use App\Vendoring\Service\Payout\VendorPayoutRequestService;
use App\Vendoring\ServiceInterface\Payout\VendorPayoutRequestServiceInterface;
use App\Vendoring\ServiceInterface\Payout\VendorPayoutServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class PayoutControllerTest extends TestCase
{
    public function testCreateReturnsValidationErrorWhenRequiredFieldIsMissing(): void
    {
        $controller = new VendorPayoutController(new FakeVendorPayoutService(), new FakePayoutRepository(), new VendorPayoutRequestService());

        $response = $controller->create(Request::create('/api/payout/create', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => 1000,
        ], JSON_THROW_ON_ERROR)));

        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('retention_fee_percent_required', $payload['error'] ?? null);
        self::assertSame('Check payout request fields and try again.', $payload['hint'] ?? null);
    }

    public function testCreateTreatsWhitespaceOnlyRetentionFeePercentAsRequiredValidationFailure(): void
    {
        $controller = new VendorPayoutController(new FakeVendorPayoutService(), new FakePayoutRepository(), new VendorPayoutRequestService());

        $response = $controller->create(Request::create('/api/payout/create', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => 1000,
            'retentionFeePercent' => '   ',
        ], JSON_THROW_ON_ERROR)));

        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('retention_fee_percent_required', $payload['error'] ?? null);
        self::assertSame('Check payout request fields and try again.', $payload['hint'] ?? null);
    }

    public function testCreateTreatsWhitespaceOnlyThresholdCentsAsRequiredValidationFailure(): void
    {
        $controller = new VendorPayoutController(new FakeVendorPayoutService(), new FakePayoutRepository(), new VendorPayoutRequestService());

        $response = $controller->create(Request::create('/api/payout/create', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => '   ',
            'retentionFeePercent' => 0.05,
        ], JSON_THROW_ON_ERROR)));

        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('threshold_cents_required', $payload['error'] ?? null);
        self::assertSame('Check payout request fields and try again.', $payload['hint'] ?? null);
    }

    public function testCreateReturnsCreatedPayload(): void
    {
        $service = new FakeVendorPayoutService('payout-1');
        $controller = new VendorPayoutController($service, new FakePayoutRepository(), new VendorPayoutRequestService());

        $response = $controller->create(Request::create('/api/payout/create', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => 1000,
            'retentionFeePercent' => 0.05,
        ], JSON_THROW_ON_ERROR)));

        $payload = self::decodePayload($response);
        $data = self::payloadData($payload);

        self::assertSame(201, $response->getStatusCode());
        self::assertTrue((bool) ($data['created'] ?? false));
        self::assertSame('payout-1', $data['payoutId'] ?? null);
        self::assertInstanceOf(VendorCreatePayoutDTO::class, $service->lastCreateDto);
        self::assertSame('tenant-1', $service->lastCreateDto->tenantId);
        self::assertSame('vendor-1', $service->lastCreateDto->vendorId);
    }

    public function testCreateAcceptsTrimmedNumericStringValuesInPayload(): void
    {
        $service = new FakeVendorPayoutService('payout-1');
        $controller = new VendorPayoutController($service, new FakePayoutRepository(), new VendorPayoutRequestService());

        $response = $controller->create(Request::create('/api/payout/create', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => ' 1000 ',
            'retentionFeePercent' => ' 0.05 ',
        ], JSON_THROW_ON_ERROR)));

        $payload = self::decodePayload($response);
        $data = self::payloadData($payload);

        self::assertSame(201, $response->getStatusCode());
        self::assertTrue((bool) ($data['created'] ?? false));
        self::assertSame('payout-1', $data['payoutId'] ?? null);
        self::assertInstanceOf(VendorCreatePayoutDTO::class, $service->lastCreateDto);
        self::assertSame(1000, $service->lastCreateDto->thresholdCents);
        self::assertSame(0.05, $service->lastCreateDto->retentionFeePercent);
    }

    public function testCreateMapsUnknownValidationFailureToStableErrorCode(): void
    {
        $requestService = new class implements VendorPayoutRequestServiceInterface {
            public function toCreateDto(array $payload): VendorCreatePayoutDTO
            {
                throw new \InvalidArgumentException('validation pipeline broke');
            }

            public function normalizePayout(VendorPayoutEntity $payout): array
            {
                return ['id' => $payout->id];
            }
        };

        $controller = new VendorPayoutController(new FakeVendorPayoutService(), new FakePayoutRepository(), $requestService);
        $response = $controller->create(Request::create('/api/payout/create', 'POST', content: '{}'));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('payout_validation_error', $payload['error'] ?? null);
        self::assertSame('Check payout request fields and try again.', $payload['hint'] ?? null);
    }

    public function testCreateMapsRetentionFeePercentOutOfRangeToDedicatedErrorCode(): void
    {
        $controller = new VendorPayoutController(new FakeVendorPayoutService(), new FakePayoutRepository(), new VendorPayoutRequestService());
        $response = $controller->create(Request::create('/api/payout/create', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => 1000,
            'retentionFeePercent' => 1.5,
        ], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('retention_fee_percent_out_of_range', $payload['error'] ?? null);
        self::assertSame('Check payout request fields and try again.', $payload['hint'] ?? null);
    }

    public function testProcessReturnsNotFoundForUnknownPendingPayout(): void
    {
        $controller = new VendorPayoutController(new FakeVendorPayoutService(null, false), new FakePayoutRepository(), new VendorPayoutRequestService());

        $response = $controller->process('missing-payout');
        $payload = self::decodePayload($response);
        $data = self::payloadData($payload);

        self::assertSame(404, $response->getStatusCode());
        self::assertFalse((bool) ($data['processed'] ?? true));
    }

    public function testGetOneReturnsStableRuntimeFailureWithoutInternalMessageLeak(): void
    {
        $repository = new class implements VendorPayoutRepositoryInterface {
            public function insert(VendorPayoutEntity $payout): void {}
            public function insertItem(VendorPayoutItemEntity $item): void {}
            public function byId(string $id): ?VendorPayoutEntity
            {
                throw new \Doctrine\DBAL\ConnectionException('sensitive_sql_message');
            }
            public function items(string $payoutId): array
            {
                return [];
            }
            public function markProcessed(string $id, string $processedAt, array $meta = []): void {}
            public function markFailed(string $id, string $processedAt, array $meta = []): void {}
        };

        $controller = new VendorPayoutController(new FakeVendorPayoutService(), $repository, new VendorPayoutRequestService());
        $response = $controller->getOne('payout-1');
        $payload = self::decodePayload($response);

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('payout_lookup_failed', $payload['error'] ?? null);
        self::assertSame('Check runtime logs for details and retry the operation.', $payload['hint'] ?? null);
        self::assertArrayNotHasKey('message', $payload);
    }

    /** @return array<string, mixed> */
    private static function decodePayload(JsonResponse $response): array
    {
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private static function payloadData(array $payload): array
    {
        $data = $payload['data'] ?? null;

        /** @var array<string, mixed> $data */
        return $data;
    }
}

final class FakeVendorPayoutService implements VendorPayoutServiceInterface
{
    public ?VendorCreatePayoutDTO $lastCreateDto = null;

    public function __construct(
        private readonly ?string $createResult = null,
        private readonly bool $processResult = true,
    ) {}

    public function create(VendorCreatePayoutDTO $dto): ?string
    {
        $this->lastCreateDto = $dto;

        return $this->createResult;
    }

    public function process(string $payoutId): bool
    {
        return $this->processResult;
    }
}

final class FakePayoutRepository implements VendorPayoutRepositoryInterface
{
    public function insert(VendorPayoutEntity $payout): void {}

    public function insertItem(VendorPayoutItemEntity $item): void {}

    public function byId(string $id): ?VendorPayoutEntity
    {
        return null;
    }

    public function items(string $payoutId): array
    {
        return [];
    }

    public function markProcessed(string $id, string $processedAt, array $meta = []): void {}

    public function markFailed(string $id, string $processedAt, array $meta = []): void {}
}
