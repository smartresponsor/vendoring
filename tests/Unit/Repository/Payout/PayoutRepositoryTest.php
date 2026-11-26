<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Repository\Payout;

use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\Repository\VendorPayoutRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())->method('findOneBy')->with(['tenantId' => 'tenant-1', 'payoutId' => 'payout-1'], null)->willReturn($payout);
        $this->entityManager->method('getRepository')->with(VendorPayoutEntity::class)->willReturn($repository);

        self::assertSame($payout, (new VendorPayoutRepository($this->entityManager))->byId('tenant-1', 'payout-1'));
    }

    public function testMarkProcessedUpdatesEntityAndFlushes(): void
    {
        $payout = $this->payout(status: 'pending', meta: ['source' => 'scheduled']);
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())->method('findOneBy')->with(['tenantId' => 'tenant-1', 'payoutId' => 'payout-1'], null)->willReturn($payout);
        $this->entityManager->method('getRepository')->willReturn($repository);
        $this->entityManager->expects(self::once())->method('flush');

        (new VendorPayoutRepository($this->entityManager))->markProcessed('tenant-1', 'payout-1', '2026-03-30 11:00:00', ['providerRef' => 'bank_ref_123']);

        self::assertSame('processed', $payout->status);
        self::assertSame('2026-03-30 11:00:00', $payout->processedAt);
        self::assertSame('scheduled', $payout->meta['source']);
        self::assertSame('bank_ref_123', $payout->meta['providerRef']);
    }

    public function testMarkFailedUpdatesEntityAndFlushes(): void
    {
        $payout = $this->payout(status: 'pending');
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())->method('findOneBy')->with(['tenantId' => 'tenant-1', 'payoutId' => 'payout-1'], null)->willReturn($payout);
        $this->entityManager->method('getRepository')->willReturn($repository);
        $this->entityManager->expects(self::once())->method('flush');

        (new VendorPayoutRepository($this->entityManager))->markFailed('tenant-1', 'payout-1', '2026-03-30 11:30:00', ['error' => 'provider_declined']);

        self::assertSame('failed', $payout->status);
        self::assertSame('2026-03-30 11:30:00', $payout->processedAt);
        self::assertSame('provider_declined', $payout->meta['error']);
    }

    /** @param array<string, mixed> $meta */
    private function payout(string $status = 'processed', array $meta = []): VendorPayoutEntity
    {
        return new VendorPayoutEntity('payout-1', 'tenant-1', 'vendor-1', 'USD', 10000, 500, 9500, $status, '2026-03-30 10:00:00', null, $meta);
    }
}
