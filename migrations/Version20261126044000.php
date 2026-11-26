<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126044000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enforce at most one pending payout per explicit tenant, vendor and currency.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Payout scope uniqueness requires PostgreSQL.');
        $duplicates = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM (SELECT tenant_id,vendor_id,currency FROM vendor_payout WHERE tenant_id IS NOT NULL AND payout_status = 'pending' GROUP BY tenant_id,vendor_id,currency HAVING COUNT(*) > 1) duplicates");
        $this->abortIf($duplicates > 0, 'Duplicate pending payouts require explicit financial reconciliation.');
        $this->addSql('ALTER TABLE vendor_payout ADD COLUMN pending_slot INT DEFAULT NULL');
        $this->addSql("UPDATE vendor_payout SET pending_slot = 1 WHERE tenant_id IS NOT NULL AND payout_status = 'pending'");
        $this->addSql("ALTER TABLE vendor_payout ADD CONSTRAINT chk_vendor_payout_pending_slot CHECK (tenant_id IS NULL OR (payout_status = 'pending' AND pending_slot IS NOT NULL AND pending_slot = 1) OR (payout_status <> 'pending' AND pending_slot IS NULL))");
        $this->addSql('CREATE UNIQUE INDEX uniq_vendor_payout_pending_scope ON vendor_payout (tenant_id, vendor_id, currency, pending_slot)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Removing payout uniqueness would weaken financial integrity.');
    }
}
