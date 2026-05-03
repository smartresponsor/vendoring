<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Repository\Payout;

use App\Vendoring\Entity\Vendor\VendorPayoutAccountEntity;
use App\Vendoring\Repository\Vendor\VendorPayoutAccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PayoutAccountRepositoryTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private ObjectRepository&MockObject $objectRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->objectRepository = $this->createMock(ObjectRepository::class);
        $this->entityManager->method('getRepository')->with(VendorPayoutAccountEntity::class)->willReturn($this->objectRepository);
    }

    public function testGetReturnsDoctrineEntityWhenFound(): void
    {
        $account = $this->account(active: true);
        $this->objectRepository->expects(self::once())->method('findOneBy')->with(['tenantId' => 'tenant-1', 'vendorId' => 'vendor-1'])->willReturn($account);

        self::assertSame($account, (new VendorPayoutAccountRepository($this->entityManager))->get('tenant-1', 'vendor-1'));
    }

    public function testUpsertUpdatesExistingTenantVendorAccount(): void
    {
        $existing = $this->account(provider: 'old', active: true);
        $incoming = $this->account(provider: 'bank', active: false);
        $this->objectRepository->method('findOneBy')->willReturn($existing);
        $this->entityManager->expects(self::never())->method('persist');
        $this->entityManager->expects(self::once())->method('flush');

        (new VendorPayoutAccountRepository($this->entityManager))->upsert($incoming);

        self::assertSame('bank', $existing->provider);
        self::assertFalse($existing->active);
    }

    public function testUpsertPersistsNewTenantVendorAccountWhenItDoesNotExist(): void
    {
        $account = $this->account(active: true);
        $this->objectRepository->method('findOneBy')->willReturn(null);
        $this->entityManager->expects(self::once())->method('persist')->with($account);
        $this->entityManager->expects(self::once())->method('flush');

        (new VendorPayoutAccountRepository($this->entityManager))->upsert($account);
    }

    private function account(string $provider = 'bank', bool $active = true): VendorPayoutAccountEntity
    {
        return new VendorPayoutAccountEntity('acc-1', 'tenant-1', 'vendor-1', $provider, 'iban-123', 'USD', $active, '2026-03-30 10:00:00');
    }
}
