<?php

declare(strict_types=1);

namespace App\Tests\Unit\Statement;

use App\Entity\Vendor;
use App\Entity\VendorBilling;
use App\RepositoryInterface\VendorBillingRepositoryInterface;
use App\Service\Statement\VendorStatementRecipientProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorStatementRecipientProviderTest extends TestCase
{
    private VendorBillingRepositoryInterface&MockObject $billings;

    protected function setUp(): void
    {
        $this->billings = $this->createMock(VendorBillingRepositoryInterface::class);
    }

    public function testForPeriodBuildsRecipientsFromBillingEmails(): void
    {
        $billingA = $this->billingWithVendorAndEmail(101, 'billing-a@example.com');
        $billingB = $this->billingWithVendorAndEmail(202, ' billing-b@example.com ');

        $this->billings->expects(self::once())->method('findAll')->willReturn([$billingA, $billingB]);

        $recipients = (new VendorStatementRecipientProvider($this->billings))->forPeriod('2026-03-01', '2026-03-31');

        self::assertCount(2, $recipients);
        self::assertSame('default', $recipients[0]->tenantId);
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

        $recipients = (new VendorStatementRecipientProvider($this->billings))->forPeriod('2026-03-01', '2026-03-31');

        self::assertSame([], $recipients);
    }

    private function billingWithVendorAndEmail(?int $vendorId, string $email): VendorBilling
    {
        $vendor = new Vendor('Vendor Example', 10);
        $vendorReflection = new \ReflectionObject($vendor);
        $vendorIdProperty = $vendorReflection->getProperty('id');
        $vendorIdProperty->setValue($vendor, $vendorId);

        $billing = new VendorBilling($vendor);
        $billingReflection = new \ReflectionObject($billing);
        $billingEmailProperty = $billingReflection->getProperty('billingEmail');
        $billingEmailProperty->setValue($billing, $email);

        return $billing;
    }
}
