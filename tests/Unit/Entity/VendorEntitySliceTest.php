<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Entity;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorApiKeyEntity;
use App\Vendoring\Entity\Vendor\VendorBillingEntity;
use App\Vendoring\Entity\Vendor\VendorDocumentEntity;
use App\Vendoring\Entity\Vendor\VendorPassportEntity;
use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use PHPUnit\Framework\TestCase;

final class VendorEntitySliceTest extends TestCase
{
    public function testVendorLifecycleAndApiKeyPermissions(): void
    {
        $vendor = new VendorEntity('Acme', 42);
        self::assertSame('Acme', $vendor->getBrandName());
        self::assertSame('inactive', $vendor->getStatus());

        $vendor->rename('Acme Pro');
        $vendor->activate();

        self::assertSame('Acme Pro', $vendor->getBrandName());
        self::assertSame('active', $vendor->getStatus());

        $key = new VendorApiKeyEntity($vendor, 'hash-1', 'read,write');
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
        $vendor = new VendorEntity('Vendor X');

        $billing = new VendorBillingEntity($vendor);
        $billing->markPayoutRequested();
        self::assertSame('requested', $billing->getPayoutStatus());
        $billing->markPayoutCompleted();
        self::assertSame('completed', $billing->getPayoutStatus());

        $passport = new VendorPassportEntity($vendor, 'TAX-1', 'US');
        self::assertFalse($passport->isVerified());
        $passport->markVerified();
        self::assertTrue($passport->isVerified());

        $document = new VendorDocumentEntity($vendor, 'license', '/tmp/license.pdf');
        self::assertSame('license', $document->getType());
        self::assertSame('/tmp/license.pdf', $document->getFilePath());

        $transaction = new VendorTransactionEntity('vendor-1', 'order-1', 'project-1', '10.00');
        self::assertSame('pending', $transaction->getStatus());
        $transaction->setStatus('settled');
        self::assertSame('settled', $transaction->getStatus());
    }
}
