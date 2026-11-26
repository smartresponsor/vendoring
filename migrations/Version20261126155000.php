<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126155000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Persist authenticated transaction ownership; do not infer owners for historical rows.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Transaction ownership migration requires PostgreSQL.');
        $this->addSql('ALTER TABLE vendor_transaction ADD COLUMN owner_vendor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vendor_transaction ADD CONSTRAINT fk_vendor_transaction_owner FOREIGN KEY (owner_vendor_id) REFERENCES vendor (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_vendor_transaction_owner ON vendor_transaction (owner_vendor_id)');
        $this->addSql('ALTER TABLE vendor_transaction ADD CONSTRAINT chk_vendor_transaction_owner_identity CHECK (owner_vendor_id IS NULL OR vendor_id = CAST(owner_vendor_id AS VARCHAR))');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Dropping verified transaction ownership would weaken authorization.');
    }
}
