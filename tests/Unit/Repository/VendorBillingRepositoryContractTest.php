<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Repository;

use PHPUnit\Framework\TestCase;

final class VendorBillingRepositoryContractTest extends TestCase
{
    public function testFindOneByVendorIdUsesRelationJoinInsteadOfScalarVendorIdField(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Repository/Vendor/VendorBillingRepository.php');

        self::assertStringContainsString("->innerJoin('billing.vendor', 'vendor')", $source);
        self::assertStringContainsString("->andWhere('vendor.id = :vendorId')", $source);
        self::assertStringContainsString('->setParameter(\'vendorId\', (int) $vendorId)', $source);
        self::assertStringNotContainsString('findOneBy([\'vendorId\' => $vendorId])', $source);
    }
}
