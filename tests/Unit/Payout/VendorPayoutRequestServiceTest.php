<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Payout;

use App\Entity\Payout\Payout;
use App\Service\Payout\VendorPayoutRequestService;
use PHPUnit\Framework\TestCase;

final class VendorPayoutRequestServiceTest extends TestCase
{
    public function testToCreateDtoRequiresTenantAwarePayoutPayload(): void
    {
        $service = new VendorPayoutRequestService();
        $dto = $service->toCreateDto([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => '1000',
            'retentionFeePercent' => '0.05',
        ]);

        self::assertSame('tenant-1', $dto->tenantId);
        self::assertSame('vendor-1', $dto->vendorId);
        self::assertSame('USD', $dto->currency);
        self::assertSame(1000, $dto->thresholdCents);
        self::assertSame(0.05, $dto->retentionFeePercent);
    }

    public function testToCreateDtoRejectsMissingTenantId(): void
    {
        $service = new VendorPayoutRequestService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('tenantId required');

        $service->toCreateDto([
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => 1000,
            'retentionFeePercent' => 0.05,
        ]);
    }

    public function testNormalizePayoutIncludesMetaForOperationalReadback(): void
    {
        $service = new VendorPayoutRequestService();
        $payout = new Payout(
            id: 'payout-1',
            vendorId: 'vendor-1',
            currency: 'USD',
            grossCents: 1000,
            feeCents: 50,
            netCents: 950,
            status: 'processed',
            createdAt: '2026-03-30 10:00:00',
            processedAt: '2026-03-30 11:00:00',
            meta: ['tenantId' => 'tenant-1', 'providerRef' => 'bank_ref_123'],
        );

        $normalized = $service->normalizePayout($payout);

        self::assertSame('payout-1', $normalized['id']);
        self::assertIsArray($normalized['meta'] ?? null);
        self::assertSame('tenant-1', $normalized['meta']['tenantId'] ?? null);
        self::assertSame('bank_ref_123', $normalized['meta']['providerRef'] ?? null);
    }

    public function testToCreateDtoRejectsRetentionFeePercentOutsideExpectedRange(): void
    {
        $service = new VendorPayoutRequestService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('retentionFeePercent out_of_range');

        $service->toCreateDto([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => 1000,
            'retentionFeePercent' => 1.5,
        ]);
    }
}
