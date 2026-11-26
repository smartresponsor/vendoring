<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915131500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize Vendoring Objecting columns to the entity-native contract.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL only.');
        $renames = [
            'object_uuid' => 'uuid', 'object_slug' => 'slug',
            'object_created_at' => 'created_at', 'object_modified_at' => 'modified_at',
            'object_created_by' => 'created_by', 'object_modified_by' => 'modified_by',
            'object_active' => 'active', 'object_enabled' => 'enabled',
            'object_status' => 'status',
            'object_first_title' => 'first_title', 'object_middle_title' => 'middle_title', 'object_last_title' => 'last_title',
        ];

        foreach ($schema->getTables() as $table) {
            $tableName = $table->getName();
            if ('vendor' !== $tableName && !str_starts_with($tableName, 'vendor_')) {
                continue;
            }
            foreach ($renames as $from => $to) {
                if (!$table->hasColumn($from)) {
                    continue;
                }
                $this->abortIf($table->hasColumn($to), sprintf('Both %s and %s exist on %s.', $from, $to, $tableName));
                $this->addSql(sprintf('ALTER TABLE %s RENAME COLUMN %s TO %s', $tableName, $from, $to));
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Entity-native Objecting field names are the forward schema contract.');
    }
}
