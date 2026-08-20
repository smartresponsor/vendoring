<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

final class Version20260819224500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Attach vendor service capabilities to vendors and catalog categories';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Vendoring production schema requires PostgreSQL.');
        $this->abortIf(!$schema->hasTable('vendor_service'), 'vendor_service table is required before capability adoption.');
        $this->abortIf(!$schema->hasTable('vendor'), 'vendor table is required before capability adoption.');

        $table = $schema->getTable('vendor_service');
        $needsVendor = !$table->hasColumn('vendor_id');
        $needsCategory = !$table->hasColumn('category_id');
        if (($needsVendor || $needsCategory) && (int) $this->connection->fetchOne('SELECT COUNT(*) FROM vendor_service') > 0) {
            $this->abortIf(true, 'vendor_service contains legacy rows without owner/category relation; explicit backfill is required before adoption.');
        }

        if ($needsVendor) {
            $this->addSql('ALTER TABLE vendor_service ADD vendor_id INT DEFAULT NULL');
        }
        if ($needsCategory) {
            $this->addSql('ALTER TABLE vendor_service ADD category_id VARCHAR(64) DEFAULT NULL');
        }

        if (!$needsVendor && !$needsCategory) {
            $this->abortIf((int) $this->connection->fetchOne('SELECT COUNT(*) FROM vendor_service WHERE vendor_id IS NULL OR category_id IS NULL') > 0, 'vendor_service contains incomplete capability ownership; explicit repair is required before constraints can be applied.');
        }

        $this->addSql('ALTER TABLE vendor_service ALTER COLUMN vendor_id SET NOT NULL');
        $this->addSql('ALTER TABLE vendor_service ALTER COLUMN category_id SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_vendor_service_vendor_category ON vendor_service (vendor_id, category_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_vendor_service_category ON vendor_service (category_id)');
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_vendor_service_vendor') THEN ALTER TABLE vendor_service ADD CONSTRAINT fk_vendor_service_vendor FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE; END IF; END $$");
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration('Vendor service capability ownership is durable production data and intentionally irreversible.');
    }
}
