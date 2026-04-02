<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Vendor;
use App\Entity\VendorApiKey;
use App\Entity\VendorBilling;
use App\Entity\VendorDocument;
use App\Entity\VendorPassport;
use App\Entity\VendorTransaction;
use PHPUnit\Framework\TestCase;

final class VendorEntitySliceTest extends TestCase
{
    public function testVendorLifecycleAndApiKeyPermissions(): void
    {
        $vendor = new Vendor('Acme', 42);
        self::assertSame('Acme', $vendor->getBrandName());
        self::assertSame('inactive', $vendor->getStatus());

        $vendor->rename('Acme Pro');
        $vendor->activate();

        self::assertSame('Acme Pro', $vendor->getBrandName());
        self::assertSame('active', $vendor->getStatus());

        $key = new VendorApiKey($vendor, 'hash-1', 'read,write');
        self::assertTrue($key->hasPermission('read'));
        self::assertTrue($key->hasPermission('write'));
        self::assertFalse($key->hasPermission('admin'));
        self::assertSame('active', $key->getStatus());

        $key->touch();
        $key->deactivate();

        self::assertNotNull($key->getLastUsedAt());
        self::assertSame('inactive', $key->getStatus());
    }

    public function testBillingPassportDocumentAndTransactionHaveMinimalBehavior(): void
    {
        $vendor = new Vendor('Vendor X');

        $billing = new VendorBilling($vendor);
        $billing->markPayoutRequested();
        self::assertSame('requested', $billing->getPayoutStatus());
        $billing->markPayoutCompleted();
        self::assertSame('completed', $billing->getPayoutStatus());

        $passport = new VendorPassport($vendor, 'TAX-1', 'US');
        self::assertFalse($passport->isVerified());
        $passport->markVerified();
        self::assertTrue($passport->isVerified());

        $document = new VendorDocument($vendor, 'license', '/tmp/license.pdf');
        self::assertSame('license', $document->getType());
        self::assertSame('/tmp/license.pdf', $document->getFilePath());

        $transaction = new VendorTransaction('vendor-1', 'order-1', 'project-1', '10.00');
        self::assertSame('pending', $transaction->getStatus());
        $transaction->setStatus('settled');
        self::assertSame('settled', $transaction->getStatus());
    }
}
