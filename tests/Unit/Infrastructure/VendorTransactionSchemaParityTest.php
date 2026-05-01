<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class VendorTransactionSchemaParityTest extends TestCase
{
    public function testEntityDoesNotPretendFullVendorOrderProjectUniqueConstraint(): void
    {
        $entityPath = dirname(__DIR__, 3) . '/src/Entity/VendorTransactionEntity.php';

        self::assertFileExists($entityPath);
        $contents = (string) file_get_contents($entityPath);

        self::assertStringNotContainsString('vendor_order_project_unique', $contents);
    }
}
