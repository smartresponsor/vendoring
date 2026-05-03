<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Payout;

use App\Vendoring\Entity\Vendor\VendorPayoutAccountEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorPayoutAccountRepositoryInterface;
use App\Vendoring\Service\Payout\VendorPayoutAccountService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorPayoutAccountServiceTest extends TestCase
{
    private VendorPayoutAccountRepositoryInterface&MockObject $accounts;

    protected function setUp(): void
    {
        $this->accounts = $this->createMock(VendorPayoutAccountRepositoryInterface::class);
    }

    public function testUpsertFromPayloadNormalizesTrimmedStringsAndUppercasesCurrency(): void
    {
        $this->accounts
            ->expects(self::once())
            ->method('upsert')
            ->with(self::callback(function (VendorPayoutAccountEntity $account): bool {
                self::assertSame('tenant-1', $account->tenantId);
                self::assertSame('vendor-1', $account->vendorId);
                self::assertSame('bank', $account->provider);
                self::assertSame('iban-123', $account->accountRef);
                self::assertSame('USD', $account->currency);
                self::assertFalse($account->active);

                return true;
            }));

        $service = new VendorPayoutAccountService($this->accounts);
        $account = $service->upsertFromPayload([
            'tenantId' => ' tenant-1 ',
            'vendorId' => ' vendor-1 ',
            'provider' => ' bank ',
            'accountRef' => ' iban-123 ',
            'currency' => ' usd ',
            'active' => '0',
        ]);

        self::assertSame('tenant-1', $account->tenantId);
        self::assertSame('vendor-1', $account->vendorId);
        self::assertSame('bank', $account->provider);
        self::assertSame('iban-123', $account->accountRef);
        self::assertSame('USD', $account->currency);
        self::assertFalse($account->active);
    }

    public function testUpsertFromPayloadRejectsBlankRequiredFieldAfterTrim(): void
    {
        $this->accounts->expects(self::never())->method('upsert');

        $service = new VendorPayoutAccountService($this->accounts);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('accountRef required');

        $service->upsertFromPayload([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'provider' => 'bank',
            'accountRef' => '   ',
            'currency' => 'USD',
        ]);
    }
}
