<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class TransactionMigrationContractTest extends TestCase
{
    public function testPostgresMigrationContainsVendorTransactionTable(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql');

        self::assertIsString($source);
        self::assertStringContainsString('CREATE TABLE vendor_transaction', $source);
        self::assertStringContainsString('amount NUMERIC(12,2) NOT NULL CHECK (amount > 0)', $source);
    }

    public function testSqliteMigrationContainsVendorTransactionTable(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql');

        self::assertIsString($source);
        self::assertStringContainsString('CREATE TABLE vendor_transaction', $source);
        self::assertStringContainsString('amount NUMERIC(12,2) NOT NULL CHECK (amount > 0)', $source);
    }

    public function testUniqueIndexesExistForNullAndNonNullProjectId(): void
    {
        $pg = file_get_contents(__DIR__ . '/../../../migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql');
        $sqlite = file_get_contents(__DIR__ . '/../../../migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql');

        self::assertIsString($pg);
        self::assertIsString($sqlite);
        self::assertStringContainsString('uniq_vendor_transaction_vendor_order_project_nonnull', $pg);
        self::assertStringContainsString('uniq_vendor_transaction_vendor_order_nullproject', $pg);
        self::assertStringContainsString('uniq_vendor_transaction_vendor_order_project_nonnull', $sqlite);
        self::assertStringContainsString('uniq_vendor_transaction_vendor_order_nullproject', $sqlite);
    }

    public function testStatusCheckConstraintExistsInPostgresMigration(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql');

        self::assertIsString($source);
        self::assertStringContainsString("CHECK (status IN ('pending', 'authorized', 'failed', 'cancelled', 'settled', 'refunded'))", $source);
    }

    public function testStatusCheckConstraintExistsInSqliteMigration(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql');

        self::assertIsString($source);
        self::assertStringContainsString("CHECK (status IN ('pending', 'authorized', 'failed', 'cancelled', 'settled', 'refunded'))", $source);
    }
}
