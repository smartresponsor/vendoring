<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Entity\Vendor;

use App\Vendoring\Entity\Vendor\VendorEntity;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

final class VendorEntityObjectIdentityContractTest extends TestCase
{
    public function testVendorEntityUsesCanonicalObjectIdentityContract(): void
    {
        $vendor = new VendorEntity('Acme Supply');
        $objectUuid = $vendor->getObjectUuid();

        self::assertSame(26, \strlen($objectUuid));
        self::assertInstanceOf(UuidV7::class, Uuid::fromString($objectUuid));
        self::assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $objectUuid);
        self::assertSame($objectUuid, $vendor->getObjectSlug());
        self::assertNull($vendor->getId());

        $vendor->setObjectSlug('acme-supply');

        self::assertSame($objectUuid, $vendor->getObjectUuid());
        self::assertSame('acme-supply', $vendor->getObjectSlug());
        self::assertSame('inactive', $vendor->getStatus());
    }
}
