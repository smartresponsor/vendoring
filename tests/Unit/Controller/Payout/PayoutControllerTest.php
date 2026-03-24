<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Controller\Payout;

use App\Controller\Payout\PayoutController;
use App\DTO\Payout\CreatePayoutDTO;
use App\Entity\Vendor\Payout\Payout;
use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use App\ServiceInterface\Payout\PayoutRequestServiceInterface;
use App\ServiceInterface\Payout\PayoutServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class PayoutControllerTest extends TestCase
{
    public function testCreateReturnsValidationErrorWhenRequiredFieldIsMissing(): void
    {
        $controller = new PayoutController(new FakePayoutService(), new FakePayoutRepository(), new FakePayoutRequestService());

        $response = $controller->create(Request::create('/api/payout/create', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => 1000,
        ], JSON_THROW_ON_ERROR)));

        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('retentionFeePercent required', $payload['error']);
    }

    public function testCreateReturnsCreatedPayload(): void
    {
        $service = new FakePayoutService('payout-1');
        $controller = new PayoutController($service, new FakePayoutRepository(), new FakePayoutRequestService());

        $response = $controller->create(Request::create('/api/payout/create', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => 1000,
            'retentionFeePercent' => 0.05,
        ], JSON_THROW_ON_ERROR)));

        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(201, $response->getStatusCode());
        self::assertTrue($payload['data']['created']);
        self::assertSame('payout-1', $payload['data']['payoutId']);
        self::assertInstanceOf(CreatePayoutDTO::class, $service->lastCreateDto);
        self::assertSame('vendor-1', $service->lastCreateDto?->vendorId);
    }

    public function testProcessReturnsNotFoundForUnknownPendingPayout(): void
    {
        $controller = new PayoutController(new FakePayoutService(null, false), new FakePayoutRepository(), new FakePayoutRequestService());

        $response = $controller->process('missing-payout');
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(404, $response->getStatusCode());
        self::assertFalse($payload['data']['processed']);
    }
}

final class FakePayoutService implements PayoutServiceInterface
{
    public ?CreatePayoutDTO $lastCreateDto = null;

    public function __construct(
        private readonly ?string $createResult = null,
        private readonly bool $processResult = true,
    ) {
    }

    public function create(CreatePayoutDTO $dto): ?string
    {
        $this->lastCreateDto = $dto;

        return $this->createResult;
    }

    public function process(string $payoutId): bool
    {
        return $this->processResult;
    }
}

final class FakePayoutRepository implements PayoutRepositoryInterface
{
    public function insert(Payout $payout): void
    {
    }

    public function insertItem(\App\Entity\Vendor\Payout\PayoutItem $item): void
    {
    }

    public function byId(string $id): ?Payout
    {
        return null;
    }

    public function items(string $payoutId): array
    {
        return [];
    }

    public function markProcessed(string $id, string $processedAt): void
    {
    }
}

final class FakePayoutRequestService implements PayoutRequestServiceInterface
{
    public function toCreateDto(array $payload): CreatePayoutDTO
    {
        foreach (['vendorId', 'currency', 'thresholdCents', 'retentionFeePercent'] as $field) {
            if (!isset($payload[$field])) {
                throw new \InvalidArgumentException(sprintf('%s required', $field));
            }
        }

        return new CreatePayoutDTO(
            (string) $payload['vendorId'],
            (string) $payload['currency'],
            (int) $payload['thresholdCents'],
            (float) $payload['retentionFeePercent'],
        );
    }

    public function normalizePayout(Payout $payout): array
    {
        return ['id' => $payout->id];
    }
}
