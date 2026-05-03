<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Repository\Payout;

use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\Repository\Vendor\VendorPayoutRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PayoutRepositoryTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    public function testByIdReturnsDoctrineEntityWhenFound(): void
    {
        $payout = $this->payout();
        $this->entityManager->expects(self::once())->method('find')->with(VendorPayoutEntity::class, 'payout-1')->willReturn($payout);

        self::assertSame($payout, (new VendorPayoutRepository($this->entityManager))->byId('payout-1'));
    }

    public function testMarkProcessedUpdatesEntityAndFlushes(): void
    {
        $payout = $this->payout(status: 'pending', meta: ['tenantId' => 'tenant-1']);
        $this->entityManager->method('find')->willReturn($payout);
        $this->entityManager->expects(self::once())->method('flush');

        (new VendorPayoutRepository($this->entityManager))->markProcessed('payout-1', '2026-03-30 11:00:00', ['providerRef' => 'bank_ref_123']);

        self::assertSame('processed', $payout->status);
        self::assertSame('2026-03-30 11:00:00', $payout->processedAt);
        self::assertSame('tenant-1', $payout->meta['tenantId']);
        self::assertSame('bank_ref_123', $payout->meta['providerRef']);
    }

    public function testMarkFailedUpdatesEntityAndFlushes(): void
    {
        $payout = $this->payout(status: 'pending');
        $this->entityManager->method('find')->willReturn($payout);
        $this->entityManager->expects(self::once())->method('flush');

        (new VendorPayoutRepository($this->entityManager))->markFailed('payout-1', '2026-03-30 11:30:00', ['error' => 'provider_declined']);

        self::assertSame('failed', $payout->status);
        self::assertSame('2026-03-30 11:30:00', $payout->processedAt);
        self::assertSame('provider_declined', $payout->meta['error']);
    }

    /** @param array<string, mixed> $meta */
    private function payout(string $status = 'processed', array $meta = []): VendorPayoutEntity
    {
        return new VendorPayoutEntity('payout-1', 'vendor-1', 'USD', 10000, 500, 9500, $status, '2026-03-30 10:00:00', null, $meta);
    }
}
