<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126041600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Restore explicit financial tenant scope; preserve unresolved historical rows without guessing ownership.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Financial tenant isolation requires PostgreSQL.');

        // Historical tenant values were removed by Version20260925020500.
        // Retain existing rows as NULL/unassigned until authoritative reconciliation.
        // NOT VALID checks enforce scope for new writes without rejecting historical rows.
        foreach (['vendor_ledger_entries', 'vendor_payout_account', 'vendor_payout', 'vendor_ledger_binding'] as $table) {
            $this->addSql(sprintf('ALTER TABLE %s ADD COLUMN tenant_id VARCHAR(64) DEFAULT NULL', $table));
            $this->addSql(sprintf('ALTER TABLE %s ADD CONSTRAINT chk_%s_tenant_scope CHECK (tenant_id IS NOT NULL AND btrim(tenant_id) <> \'\') NOT VALID', $table, $table));
        }

        $this->addSql('CREATE INDEX idx_vendor_ledger_tenant_vendor ON vendor_ledger_entries (tenant_id, vendor_id)');
        $this->addSql('CREATE INDEX idx_vendor_payout_tenant_vendor ON vendor_payout (tenant_id, vendor_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_vendor_payout_account_tenant_vendor ON vendor_payout_account (tenant_id, vendor_id)');
        $this->addSql('DROP INDEX IF EXISTS uniq_vendor_ledger_binding_ledger_vendor_id');
        $this->addSql('CREATE UNIQUE INDEX uniq_vendor_ledger_binding_tenant_vendor ON vendor_ledger_binding (tenant_id, ledger_vendor_id)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Removing financial tenant identity would discard ownership evidence.');
    }
}
