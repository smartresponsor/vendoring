<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Payout;

use App\Controller\Payout\PayoutController;
use App\DTO\Payout\CreatePayoutDTO;
use App\Entity\Vendor\Payout\Payout;
use App\Entity\Vendor\Payout\PayoutItem;
use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use App\ServiceInterface\Payout\VendorPayoutRequestServiceInterface;
use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class PayoutControllerTest extends TestCase
{
    public function testCreateReturnsValidationErrorWhenRequiredFieldIsMissing(): void
    {
        $controller = new PayoutController(new FakeVendorPayoutService(), new FakePayoutRepository(), new FakeVendorPayoutRequestService());

        $response = $controller->create(Request::create('/api/payout/create', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => 1000,
        ], JSON_THROW_ON_ERROR)));

        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('retentionFeePercent required', $payload['error'] ?? null);
    }

    public function testCreateReturnsCreatedPayload(): void
    {
        $service = new FakeVendorPayoutService('payout-1');
        $controller = new PayoutController($service, new FakePayoutRepository(), new FakeVendorPayoutRequestService());

        $response = $controller->create(Request::create('/api/payout/create', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
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
        self::assertInstanceOf(CreatePayoutDTO::class, $service->lastCreateDto);
        self::assertSame('vendor-1', $service->lastCreateDto->vendorId);
    }

    public function testProcessReturnsNotFoundForUnknownPendingPayout(): void
    {
        $controller = new PayoutController(new FakeVendorPayoutService(null, false), new FakePayoutRepository(), new FakeVendorPayoutRequestService());

        $response = $controller->process('missing-payout');
        $payload = self::decodePayload($response);
        $data = self::payloadData($payload);

        self::assertSame(404, $response->getStatusCode());
        self::assertFalse((bool) ($data['processed'] ?? true));
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

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private static function payloadData(array $payload): array
    {
        $data = $payload['data'] ?? null;

        if (!is_array($data)) {
            self::fail('Expected array data payload.');
        }

        return $data;
    }
}

final class FakeVendorPayoutService implements VendorPayoutServiceInterface
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

    public function insertItem(PayoutItem $item): void
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

final class FakeVendorPayoutRequestService implements VendorPayoutRequestServiceInterface
{
    /** @param array<string, mixed> $payload */
    public function toCreateDto(array $payload): CreatePayoutDTO
    {
        foreach (['vendorId', 'currency', 'thresholdCents', 'retentionFeePercent'] as $field) {
            if (!isset($payload[$field])) {
                throw new \InvalidArgumentException(sprintf('%s required', $field));
            }
        }

        return new CreatePayoutDTO(
            self::requiredString($payload, 'vendorId'),
            self::requiredString($payload, 'currency'),
            self::requiredInt($payload, 'thresholdCents'),
            self::requiredFloat($payload, 'retentionFeePercent'),
        );
    }

    public function normalizePayout(Payout $payout): array
    {
        return ['id' => $payout->id];
    }

    /** @param array<string, mixed> $payload */
    private static function requiredString(array $payload, string $field): string
    {
        $value = $payload[$field] ?? null;

        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        throw new \InvalidArgumentException(sprintf('%s required', $field));
    }

    /** @param array<string, mixed> $payload */
    private static function requiredInt(array $payload, string $field): int
    {
        $value = $payload[$field] ?? null;

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        throw new \InvalidArgumentException(sprintf('%s required', $field));
    }

    /** @param array<string, mixed> $payload */
    private static function requiredFloat(array $payload, string $field): float
    {
        $value = $payload[$field] ?? null;

        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        throw new \InvalidArgumentException(sprintf('%s required', $field));
    }
}
