<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Statement;

use App\Vendoring\Entity\Vendor\VendorBillingEntity;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorLedgerBindingEntity;
use App\Vendoring\Provider\Statement\VendorStatementRecipientProvider;
use App\Vendoring\RepositoryInterface\VendorBillingRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorLedgerBindingRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorStatementRecipientProviderTest extends TestCase
{
    private VendorBillingRepositoryInterface&MockObject $billings;
    private VendorLedgerBindingRepositoryInterface&MockObject $bindings;

    protected function setUp(): void
    {
        $this->billings = $this->createMock(VendorBillingRepositoryInterface::class);
        $this->bindings = $this->createMock(VendorLedgerBindingRepositoryInterface::class);
    }

    public function testForPeriodBuildsRecipientsFromBillingEmails(): void
    {
        $billingA = $this->billingWithVendorAndEmail(101, 'billing-a@example.com');
        $billingB = $this->billingWithVendorAndEmail(202, ' billing-b@example.com ');

        $this->billings->expects(self::once())->method('findAll')->willReturn([$billingA, $billingB]);
        $this->bindings->expects(self::exactly(2))->method('findOneBy')->willReturnCallback(static function (array $criteria): VendorLedgerBindingEntity {
            $vendor = $criteria['vendor'];
            self::assertInstanceOf(VendorEntity::class, $vendor);
            self::assertNotNull($vendor->getId());

            return new VendorLedgerBindingEntity($vendor, 'tenant-'.(101 === $vendor->getId() ? 'a' : 'b'), (string) $vendor->getId());
        });

        $recipients = (new VendorStatementRecipientProvider($this->billings, $this->bindings))->forPeriod('2026-03-01', '2026-03-31');

        self::assertCount(2, $recipients);
        self::assertSame('tenant-a', $recipients[0]->tenantId);
        self::assertSame('tenant-b', $recipients[1]->tenantId);
        self::assertSame('101', $recipients[0]->vendorId);
        self::assertSame('billing-a@example.com', $recipients[0]->email);
        self::assertSame('USD', $recipients[0]->currency);
        self::assertSame('202', $recipients[1]->vendorId);
        self::assertSame('billing-b@example.com', $recipients[1]->email);
    }

    public function testForPeriodSkipsBlankEmailAndMissingVendorId(): void
    {
        $billingWithoutEmail = $this->billingWithVendorAndEmail(101, '   ');
        $billingWithoutVendorId = $this->billingWithVendorAndEmail(null, 'billing@example.com');

        $this->billings->expects(self::once())->method('findAll')->willReturn([$billingWithoutEmail, $billingWithoutVendorId]);

        $recipients = (new VendorStatementRecipientProvider($this->billings, $this->bindings))->forPeriod('2026-03-01', '2026-03-31');

        self::assertSame([], $recipients);
    }

    public function testUnboundVendorIsNotRecipient(): void
    {
        $billing = $this->billingWithVendorAndEmail(101, 'billing@example.com');
        $this->billings->method('findAll')->willReturn([$billing]);
        $this->bindings->expects(self::once())->method('findOneBy')->willReturn(null);
        self::assertSame([], (new VendorStatementRecipientProvider($this->billings, $this->bindings))->forPeriod('2026-03-01', '2026-03-31'));
    }

    private function billingWithVendorAndEmail(?int $vendorId, string $email): VendorBillingEntity
    {
        $vendor = new VendorEntity('Vendor Example', 10);
        $vendorReflection = new \ReflectionObject($vendor);
        $vendorIdProperty = $vendorReflection->getProperty('id');
        $vendorIdProperty->setValue($vendor, $vendorId);

        $billing = new VendorBillingEntity($vendor);
        $billingReflection = new \ReflectionObject($billing);
        $billingEmailProperty = $billingReflection->getProperty('billingEmail');
        $billingEmailProperty->setValue($billing, $email);

        return $billing;
    }
}
