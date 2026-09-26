<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260925020500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Collapse legacy tenant identity columns onto canonical Vendor identity.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Vendoring Vendor identity migration requires PostgreSQL.',
        );

        $this->addSql(
            "UPDATE vendor_ledger_entries SET vendor_id = tenant_id WHERE (vendor_id IS NULL OR vendor_id = '') AND tenant_id IS NOT NULL",
        );
        $this->addSql('ALTER TABLE vendor_ledger_entries ALTER COLUMN vendor_id SET NOT NULL');
        $this->addSql('ALTER TABLE vendor_ledger_entries DROP COLUMN IF EXISTS tenant_id');
        $this->addSql('ALTER TABLE vendor_payout_account DROP COLUMN IF EXISTS tenant_id');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'Canonical Vendor identity must not be split back into a parallel tenant identity.',
        );
    }
