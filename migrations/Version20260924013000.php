<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924013000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace Doctrine hash-named business unique indexes with deterministic Vendoring identifiers.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Vendoring schema normalization requires PostgreSQL.');

        $this->addSql('DROP INDEX IF EXISTS UNIQ_F3B052ABAB3AE574');
        $this->addSql('DROP INDEX IF EXISTS UNIQ_2DEE01C89B6B5FBA');
        $this->addSql('DROP INDEX IF EXISTS UNIQ_38E4AE90C6D61B7F');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_vendor_ledger_binding_ledger_vendor_id ON vendor_ledger_binding (ledger_vendor_id)');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_vendor_payout_account_business_id ON vendor_payout_account (account_id)');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_vendor_payout_business_id ON vendor_payout (payout_id)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Deterministic physical identifiers are canonical and must not be reverted to Doctrine hash names.');
    }
}
