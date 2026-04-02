<?php

declare(strict_types=1);

namespace App\Tests\Unit\Payout;

use App\DTO\Ledger\LedgerEntryDTO;
use App\DTO\Payout\CreatePayoutDTO;
use App\Entity\Payout\Payout;
use App\Entity\Payout\PayoutAccount;
use App\Observability\Service\MetricEmitter;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\RepositoryInterface\Payout\PayoutAccountRepositoryInterface;
use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use App\Service\Payout\VendorPayoutService;
use App\ServiceInterface\Ledger\VendorLedgerServiceInterface;
use App\ServiceInterface\Payout\VendorPayoutProviderServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorPayoutServiceTest extends TestCase
{
    private PayoutRepositoryInterface&MockObject $repo;
    private LedgerEntryRepositoryInterface&MockObject $ledgerRepo;
    private VendorLedgerServiceInterface&MockObject $ledger;
    private PayoutAccountRepositoryInterface&MockObject $accounts;
    private VendorPayoutProviderServiceInterface&MockObject $provider;
    private MetricEmitter $metrics;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(PayoutRepositoryInterface::class);
        $this->ledgerRepo = $this->createMock(LedgerEntryRepositoryInterface::class);
        $this->ledger = $this->createMock(VendorLedgerServiceInterface::class);
        $this->accounts = $this->createMock(PayoutAccountRepositoryInterface::class);
        $this->provider = $this->createMock(VendorPayoutProviderServiceInterface::class);
        $this->metrics = new MetricEmitter();
    }

    public function testCreatePersistsPendingPayoutAndLedgerReserveWithTenantId(): void
    {
        $this->ledgerRepo
            ->expects(self::once())
            ->method('balancesForVendor')
            ->with('vendor-1')
            ->willReturn([(object) ['currency' => 'USD', 'balanceCents' => 10000]]);

        $this->repo
            ->expects(self::once())
            ->method('insert')
            ->with(self::callback(function (Payout $payout): bool {
                self::assertSame('vendor-1', $payout->vendorId);
                self::assertSame('USD', $payout->currency);
                self::assertSame(10000, $payout->grossCents);
                self::assertSame(500, $payout->feeCents);
                self::assertSame(9500, $payout->netCents);
                self::assertSame('pending', $payout->status);
                self::assertSame('tenant-1', $payout->meta['tenantId']);

                return true;
            }));

        $this->ledger
            ->expects(self::once())
            ->method('record')
            ->with(self::callback(function (LedgerEntryDTO $dto): bool {
                self::assertSame('payout_reserve', $dto->type);
                self::assertSame('tenant-1', $dto->tenantId);
                self::assertSame('vendor-1', $dto->vendorId);
                self::assertSame(9500, $dto->amountCents);
                self::assertSame('USD', $dto->currency);

                return true;
            }));

        $this->accounts->expects(self::never())->method('get');
        $this->provider->expects(self::never())->method('transfer');

        $payoutId = $this->buildService()->create(new CreatePayoutDTO('tenant-1', 'vendor-1', 'USD', 1000, 0.05));

        self::assertNotNull($payoutId);
        self::assertSame([
            ['name' => 'payout_created_total', 'tags' => ['currency' => 'USD']],
        ], $this->metrics->snapshot());
    }

    public function testProcessUsesProviderAndMarksProcessedWithMetadata(): void
    {
        $payout = new Payout(
            id: 'payout-1',
            vendorId: 'vendor-1',
            currency: 'USD',
            grossCents: 10000,
            feeCents: 500,
            netCents: 9500,
            status: 'pending',
            createdAt: '2026-03-30 10:00:00',
            meta: ['tenantId' => 'tenant-1'],
        );

        $this->repo->expects(self::once())->method('byId')->with('payout-1')->willReturn($payout);
        $this->accounts->expects(self::once())->method('get')->with('tenant-1', 'vendor-1')->willReturn(
            new PayoutAccount('acc-1', 'tenant-1', 'vendor-1', 'bank', 'iban-123', 'USD', true, '2026-03-30 09:00:00')
        );
        $this->provider
            ->expects(self::once())
            ->method('transfer')
            ->with('tenant-1', 'vendor-1', 'bank', 'iban-123', 95.0, 'USD')
            ->willReturn(['ok' => true, 'ref' => 'bank_ref_123', 'error' => null]);

        $this->ledger
            ->expects(self::exactly(2))
            ->method('record')
            ->with(self::callback(function (LedgerEntryDTO $dto): bool {
                self::assertSame('tenant-1', $dto->tenantId);
                self::assertSame('vendor-1', $dto->vendorId);
                self::assertSame('USD', $dto->currency);
                self::assertContains($dto->type, ['payout_processed', 'payout_fee']);
                self::assertSame('bank', $dto->meta['provider']);
                self::assertSame('bank_ref_123', $dto->meta['providerRef']);

                return true;
            }));

        $this->repo
            ->expects(self::once())
            ->method('markProcessed')
            ->with(
                'payout-1',
                self::isType('string'),
                self::callback(function (array $meta): bool {
                    self::assertSame('tenant-1', $meta['tenantId']);
                    self::assertSame('bank', $meta['provider']);
                    self::assertSame('iban-123', $meta['accountRef']);
                    self::assertSame('bank_ref_123', $meta['providerRef']);

                    return true;
                })
            );
        $this->repo->expects(self::never())->method('markFailed');

        self::assertTrue($this->buildService()->process('payout-1'));
        self::assertSame([
            ['name' => 'payout_processed_total', 'tags' => ['currency' => 'USD', 'provider' => 'bank']],
        ], $this->metrics->snapshot());
    }

    public function testProcessMarksFailedWhenPayoutAccountIsUnavailable(): void
    {
        $payout = new Payout(
            id: 'payout-1',
            vendorId: 'vendor-1',
            currency: 'USD',
            grossCents: 10000,
            feeCents: 0,
            netCents: 10000,
            status: 'pending',
            createdAt: '2026-03-30 10:00:00',
            meta: ['tenantId' => 'tenant-1'],
        );

        $this->repo->expects(self::once())->method('byId')->willReturn($payout);
        $this->accounts->expects(self::once())->method('get')->with('tenant-1', 'vendor-1')->willReturn(null);
        $this->provider->expects(self::never())->method('transfer');
        $this->ledger->expects(self::never())->method('record');
        $this->repo->expects(self::never())->method('markProcessed');
        $this->repo
            ->expects(self::once())
            ->method('markFailed')
            ->with('payout-1', self::isType('string'), self::callback(function (array $meta): bool {
                self::assertSame('tenant-1', $meta['tenantId']);
                self::assertSame('payout_account_unavailable', $meta['error']);

                return true;
            }));

        self::assertFalse($this->buildService()->process('payout-1'));
        self::assertSame([
            ['name' => 'payout_failed_total', 'tags' => ['currency' => 'USD']],
        ], $this->metrics->snapshot());
    }

    public function testProcessMarksFailedWhenProviderReturnsError(): void
    {
        $payout = new Payout(
            id: 'payout-1',
            vendorId: 'vendor-1',
            currency: 'USD',
            grossCents: 10000,
            feeCents: 0,
            netCents: 10000,
            status: 'pending',
            createdAt: '2026-03-30 10:00:00',
            meta: ['tenantId' => 'tenant-1'],
        );

        $this->repo->expects(self::once())->method('byId')->willReturn($payout);
        $this->accounts->expects(self::once())->method('get')->with('tenant-1', 'vendor-1')->willReturn(
            new PayoutAccount('acc-1', 'tenant-1', 'vendor-1', 'bank', 'iban-123', 'USD', true, '2026-03-30 09:00:00')
        );
        $this->provider
            ->expects(self::once())
            ->method('transfer')
            ->willReturn(['ok' => false, 'ref' => 'bank_ref_123', 'error' => 'provider_declined']);
        $this->ledger->expects(self::never())->method('record');
        $this->repo->expects(self::never())->method('markProcessed');
        $this->repo
            ->expects(self::once())
            ->method('markFailed')
            ->with('payout-1', self::isType('string'), self::callback(function (array $meta): bool {
                self::assertSame('tenant-1', $meta['tenantId']);
                self::assertSame('bank', $meta['provider']);
                self::assertSame('iban-123', $meta['accountRef']);
                self::assertSame('bank_ref_123', $meta['providerRef']);
                self::assertSame('provider_declined', $meta['error']);

                return true;
            }));

        self::assertFalse($this->buildService()->process('payout-1'));
        self::assertSame([
            ['name' => 'payout_failed_total', 'tags' => ['currency' => 'USD', 'provider' => 'bank']],
        ], $this->metrics->snapshot());
    }

    private function buildService(): VendorPayoutService
    {
        return new VendorPayoutService(
            $this->repo,
            $this->ledgerRepo,
            $this->ledger,
            $this->accounts,
            $this->provider,
            $this->metrics,
        );
    }
}
