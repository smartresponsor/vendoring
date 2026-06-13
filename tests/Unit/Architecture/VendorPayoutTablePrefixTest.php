<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Architecture;

use App\Vendoring\Entity\Vendor\VendorPayoutAccountEntity;
use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\Entity\Vendor\VendorPayoutItemEntity;
use Doctrine\ORM\Mapping\Table;
use PHPUnit\Framework\TestCase;

final class VendorPayoutTablePrefixTest extends TestCase
{
    /** @return iterable<string, array{0: class-string, 1: string}> */
    public static function payoutEntityTableProvider(): iterable
    {
        yield 'payout' => [VendorPayoutEntity::class, 'vendor_payout'];
        yield 'payout_item' => [VendorPayoutItemEntity::class, 'vendor_payout_item'];
        yield 'payout_account' => [VendorPayoutAccountEntity::class, 'vendor_payout_account'];
    }

    /** @dataProvider payoutEntityTableProvider */
    public function testPayoutEntitiesUseVendorTablePrefix(string $entityClass, string $expectedTableName): void
    {
        $reflection = new \ReflectionClass($entityClass);
        $attributes = $reflection->getAttributes(Table::class);

        self::assertNotSame([], $attributes);
        self::assertSame($expectedTableName, $attributes[0]->newInstance()->nameEntity);
        self::assertStringStartsWith('vendor_', $attributes[0]->newInstance()->nameEntity);
    }
}
