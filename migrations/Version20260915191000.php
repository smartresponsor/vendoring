<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915191000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align remaining Vendoring physical column and service indexes with current ORM metadata.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Vendoring schema normalization requires PostgreSQL.');

        if ($schema->hasTable('vendor_catalog_category_html_block')) {
            $table = $schema->getTable('vendor_catalog_category_html_block');
            $hasLegacy = $table->hasColumn('object_published_at');
            $hasCanonical = $table->hasColumn('published_at');
            $this->abortIf($hasLegacy && $hasCanonical, 'Ambiguous vendor_catalog_category_html_block publication timestamp columns.');
            if ($hasLegacy && !$hasCanonical) {
                $this->addSql('ALTER TABLE vendor_catalog_category_html_block RENAME COLUMN object_published_at TO published_at');
            }
        }

        if ($schema->hasTable('vendor_service')) {
            $this->addSql('DROP INDEX IF EXISTS idx_vendor_service_category');
            $this->addSql('DROP INDEX IF EXISTS uniq_vendor_service_vendor_category');
            $this->addSql('CREATE INDEX IF NOT EXISTS IDX_FFEAA2BDF603EE73 ON vendor_service (vendor_id)');
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Vendoring physical normalization follows the current ORM contract.');
    }
}
