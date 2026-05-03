<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use PHPUnit\Framework\TestCase;

final class VendorTransactionMappingParityTest extends TestCase
{
    public function testEntityUsesExplicitSqlAlignedColumnNames(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Entity/Vendor/VendorTransactionEntity.php');

        self::assertStringContainsString("name: 'vendor_id'", $source);
        self::assertStringContainsString("name: 'order_id'", $source);
        self::assertStringContainsString("name: 'project_id'", $source);
        self::assertStringContainsString("name: 'status'", $source);
        self::assertStringContainsString("name: 'created_at'", $source);
    }

    public function testDefaultStatusUsesCanonicalStatusCatalog(): void
    {
        $transaction = new VendorTransactionEntity(
            vendorId: 'vendor-1',
            orderId: 'order-1',
            projectId: null,
            amount: '10.00',
        );

        self::assertSame('pending', $transaction->getStatus());
    }
}
