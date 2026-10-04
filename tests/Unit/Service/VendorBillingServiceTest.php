<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\DTO\VendorBillingDTO;
use App\Vendoring\Entity\Vendor\VendorBillingEntity;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Event\VendorPayoutCompletedEvent;
use App\Vendoring\Event\VendorPayoutRequestedEvent;
use App\Vendoring\RepositoryInterface\VendorBillingRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorIbanRepositoryInterface;
use App\Vendoring\Service\Billing\VendorBillingService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorBillingServiceTest extends TestCase
{
    private VendorBillingRepositoryInterface&MockObject $repository;
    private VendorIbanRepositoryInterface&MockObject $ibanRepository;
    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendorBillingRepositoryInterface::class);
        $this->ibanRepository = $this->createMock(VendorIbanRepositoryInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function testUpsertAllowsNullableBillingFieldsToBeClearedAndNormalizesStrings(): void
    {
        $vendor = new VendorEntity('Vendor Example', 10);
        $billing = new VendorBillingEntity($vendor);
        $this->primeBilling($billing, 'DE123', 'ABCDEF', 'bank', 'old@example.com');

        $this->repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['vendor' => $vendor])
            ->willReturn($billing);

        $this->repository->expects(self::once())->method('save')->with($billing, false);
        $this->repository->expects(self::once())->method('flush');
        $this->ibanRepository->expects(self::once())->method('findOneBy')->with(['vendor' => $vendor])->willReturn(null);
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
        $billing = new VendorBillingEntity(new VendorEntity('Vendor Example', 10));

        $this->repository->expects(self::once())->method('save')->with($billing, true);
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
        $billing = new VendorBillingEntity(new VendorEntity('Vendor Example', 10));

        $this->repository->expects(self::once())->method('save')->with($billing, true);
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
            $this->repository,
            $this->ibanRepository,
            $this->dispatcher,
        );
    }

    private function primeBilling(VendorBillingEntity $billing, ?string $iban, ?string $swift, string $payoutMethod, ?string $billingEmail): void
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
