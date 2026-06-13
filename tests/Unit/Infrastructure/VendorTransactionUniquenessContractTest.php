<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class VendorTransactionUniquenessContractTest extends TestCase
{
    public function testEntityDoesNotPretendFullThreeColumnUniqueConstraint(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Entity/Vendor/VendorTransactionEntity.php');

        self::assertStringNotContainsString('uniqueConstraints', $source);
        self::assertStringNotContainsString('uniq_vendor_transaction_vendor_order_project', $source);
    }

    public function testPostgresMigrationDefinesSplitPartialUniqueIndexes(): void
    {
        $sql = (string) file_get_contents(dirname(__DIR__, 3) . '/migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql');

        self::assertStringContainsString('uniq_vendor_transaction_vendor_order_project_nonnull', $sql);
        self::assertStringContainsString('uniq_vendor_transaction_vendor_order_nullproject', $sql);
        self::assertStringContainsString('WHERE project_id IS NOT NULL', $sql);
        self::assertStringContainsString('WHERE project_id IS NULL', $sql);
    }

    public function testSqliteMigrationDefinesSplitPartialUniqueIndexes(): void
    {
        $sql = (string) file_get_contents(dirname(__DIR__, 3) . '/migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql');

        self::assertStringContainsString('uniq_vendor_transaction_vendor_order_project_nonnull', $sql);
        self::assertStringContainsString('uniq_vendor_transaction_vendor_order_nullproject', $sql);
        self::assertStringContainsString('WHERE project_id IS NOT NULL', $sql);
        self::assertStringContainsString('WHERE project_id IS NULL', $sql);
    }
}
