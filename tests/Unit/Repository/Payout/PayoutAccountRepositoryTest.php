<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Repository\Payout;

use App\Vendoring\Entity\Payout\PayoutAccount;
use App\Vendoring\Repository\Payout\PayoutAccountRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PayoutAccountRepositoryTest extends TestCase
{
    private Connection&MockObject $connection;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
    }

    public function testGetHydratesPayoutAccountAndNormalizesActiveFlag(): void
    {
        $this->connection
            ->expects(self::once())
            ->method('fetchAssociative')
            ->with('SELECT * FROM payout_accounts WHERE tenant_id=:t AND vendor_id=:v', ['t' => 'tenant-1', 'v' => 'vendor-1'])
            ->willReturn([
                'id' => 'acc-1',
                'tenant_id' => 'tenant-1',
                'vendor_id' => 'vendor-1',
                'provider' => 'bank',
                'account_ref' => 'iban-123',
                'currency' => 'USD',
                'active' => '1',
                'created_at' => '2026-03-30 10:00:00',
            ]);

        $repository = new PayoutAccountRepository($this->connection);
        $account = $repository->get('tenant-1', 'vendor-1');

        self::assertNotNull($account);
        self::assertSame('acc-1', $account->id);
        self::assertSame('tenant-1', $account->tenantId);
        self::assertSame('vendor-1', $account->vendorId);
        self::assertSame('bank', $account->provider);
        self::assertSame('iban-123', $account->accountRef);
        self::assertSame('USD', $account->currency);
        self::assertTrue($account->active);
    }

    public function testUpsertUpdatesExistingTenantVendorAccount(): void
    {
        $account = new PayoutAccount('acc-1', 'tenant-1', 'vendor-1', 'bank', 'iban-123', 'USD', false, '2026-03-30 10:00:00');

        $this->connection
            ->expects(self::once())
            ->method('fetchOne')
            ->with('SELECT COUNT(*) FROM payout_accounts WHERE tenant_id=:t AND vendor_id=:v', ['t' => 'tenant-1', 'v' => 'vendor-1'])
            ->willReturn('1');

        $this->connection
            ->expects(self::once())
            ->method('update')
            ->with(
                'payout_accounts',
                [
                    'provider' => 'bank',
                    'account_ref' => 'iban-123',
                    'currency' => 'USD',
                    'active' => 0,
                ],
                [
                    'tenant_id' => 'tenant-1',
                    'vendor_id' => 'vendor-1',
                ],
            );
        $this->connection->expects(self::never())->method('insert');

        (new PayoutAccountRepository($this->connection))->upsert($account);
    }

    public function testUpsertInsertsNewTenantVendorAccountWhenItDoesNotExist(): void
    {
        $account = new PayoutAccount('acc-1', 'tenant-1', 'vendor-1', 'bank', 'iban-123', 'USD', true, '2026-03-30 10:00:00');

        $this->connection
            ->expects(self::once())
            ->method('fetchOne')
            ->with('SELECT COUNT(*) FROM payout_accounts WHERE tenant_id=:t AND vendor_id=:v', ['t' => 'tenant-1', 'v' => 'vendor-1'])
            ->willReturn('0');

        $this->connection->expects(self::never())->method('update');
        $this->connection
            ->expects(self::once())
            ->method('insert')
            ->with(
                'payout_accounts',
                [
                    'id' => 'acc-1',
                    'tenant_id' => 'tenant-1',
                    'vendor_id' => 'vendor-1',
                    'provider' => 'bank',
                    'account_ref' => 'iban-123',
                    'currency' => 'USD',
                    'active' => 1,
                    'created_at' => '2026-03-30 10:00:00',
                ],
            );

        (new PayoutAccountRepository($this->connection))->upsert($account);
    }
}
