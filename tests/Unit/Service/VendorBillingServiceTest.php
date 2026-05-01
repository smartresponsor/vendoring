<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\DTO\VendorBillingDTO;
use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorBilling;
use App\Vendoring\Event\Vendor\VendorPayoutCompletedEvent;
use App\Vendoring\Event\Vendor\VendorPayoutRequestedEvent;
use App\Vendoring\RepositoryInterface\Vendor\VendorBillingRepositoryInterface;
use App\Vendoring\Service\Billing\VendorBillingService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorBillingServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private VendorBillingRepositoryInterface&MockObject $repository;
    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(VendorBillingRepositoryInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function testUpsertAllowsNullableBillingFieldsToBeClearedAndNormalizesStrings(): void
    {
        $vendor = new Vendor('Vendor Example', 10);
        $billing = new VendorBilling($vendor);
        $this->primeBilling($billing, 'DE123', 'ABCDEF', 'bank', 'old@example.com');

        $this->repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['vendor' => $vendor])
            ->willReturn($billing);

        $this->entityManager->expects(self::once())->method('persist')->with($billing);
        $this->entityManager->expects(self::once())->method('flush');
        $this->dispatcher->expects(self::never())->method('dispatch');

        $result = $this->buildService()->upsert($vendor, new VendorBillingDTO(
            vendorId: 10,
            iban: '   ',
            swift: null,
            payoutMethod: ' paypal ',
            billingEmail: '  updated@example.com  ',
        ));

        self::assertSame($billing, $result);
        self::assertNull($result->getIban());
        self::assertNull($result->getSwift());
        self::assertSame('paypal', $result->getPayoutMethod());
        self::assertSame('updated@example.com', $result->getBillingEmail());
    }

    public function testRequestPayoutFlushesAndDispatchesRequestedEvent(): void
    {
        $billing = new VendorBilling(new Vendor('Vendor Example', 10));

        $this->entityManager->expects(self::once())->method('flush');
        $this->dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (VendorPayoutRequestedEvent $event) use ($billing): bool {
                self::assertSame($billing, $event->billing);
                self::assertSame(1500, $event->amountMinor);

                return true;
            }));

        $this->buildService()->requestPayout($billing, 1500);

        self::assertSame('requested', $billing->getPayoutStatus());
    }

    public function testCompletePayoutFlushesAndDispatchesCompletedEvent(): void
    {
        $billing = new VendorBilling(new Vendor('Vendor Example', 10));

        $this->entityManager->expects(self::once())->method('flush');
        $this->dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (VendorPayoutCompletedEvent $event) use ($billing): bool {
                self::assertSame($billing, $event->billing);
                self::assertSame(2500, $event->amountMinor);

                return true;
            }));

        $this->buildService()->completePayout($billing, 2500);

        self::assertSame('completed', $billing->getPayoutStatus());
    }

    private function buildService(): VendorBillingService
    {
        return new VendorBillingService(
            $this->entityManager,
            $this->repository,
            $this->dispatcher,
        );
    }

    private function primeBilling(VendorBilling $billing, ?string $iban, ?string $swift, string $payoutMethod, ?string $billingEmail): void
    {
        $reflection = new \ReflectionObject($billing);

        foreach ([
            'iban' => $iban,
            'swift' => $swift,
            'payoutMethod' => $payoutMethod,
            'billingEmail' => $billingEmail,
        ] as $property => $value) {
            $rp = $reflection->getProperty($property);
            $rp->setValue($billing, $value);
        }
    }
}
