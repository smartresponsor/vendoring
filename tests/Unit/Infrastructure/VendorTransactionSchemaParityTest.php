<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Entity\Vendor\VendorTransaction;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use PHPUnit\Framework\TestCase;

final class VendorTransactionSchemaParityTest extends TestCase
{
    public function testEntityDeclaresVendorCreatedIndex(): void
    {
        $reflection = new \ReflectionClass(VendorTransaction::class);
        $table = $reflection->getAttributes(Table::class)[0]->newInstance();

        self::assertNotEmpty($table->indexes);

        $index = null;
        foreach ($table->indexes as $candidate) {
            if ($candidate instanceof Index && 'idx_vendor_transaction_vendor_created' === $candidate->name) {
                $index = $candidate;
                break;
            }
        }

        self::assertInstanceOf(Index::class, $index);
        self::assertSame(['vendor_id', 'created_at', 'id'], $index->columns);
    }

    public function testEntityDeclaresVendorOrderProjectUniqueConstraint(): void
    {
        $reflection = new \ReflectionClass(VendorTransaction::class);
        $table = $reflection->getAttributes(Table::class)[0]->newInstance();

        self::assertNotEmpty($table->uniqueConstraints);

        $constraint = null;
        foreach ($table->uniqueConstraints as $candidate) {
            if ($candidate instanceof UniqueConstraint && 'uniq_vendor_transaction_vendor_order_project' === $candidate->name) {
                $constraint = $candidate;
                break;
            }
        }

        self::assertInstanceOf(UniqueConstraint::class, $constraint);
        self::assertSame(['vendor_id', 'order_id', 'project_id'], $constraint->columns);
    }

    public function testSqlMigrationsKeepVendorCreatedIndexAndNullAwareUniqueness(): void
    {
        $pg = (string) file_get_contents(__DIR__.'/../../../migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql');
        $sqlite = (string) file_get_contents(__DIR__.'/../../../migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql');

        foreach ([$pg, $sqlite] as $sql) {
            self::assertStringContainsString('idx_vendor_transaction_vendor_created', $sql);
            self::assertStringContainsString('uniq_vendor_transaction_vendor_order_project_nonnull', $sql);
            self::assertStringContainsString('uniq_vendor_transaction_vendor_order_nullproject', $sql);
        }
    }
}
