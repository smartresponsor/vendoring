<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915132000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize Vendoring Objecting code/publication columns.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL only.');
        foreach ([
            'vendor_catalog_category_banner' => ['object_code' => 'code', 'object_published' => 'published', 'object_published_at' => 'published_at'],
            'vendor_catalog_category_html_block' => ['object_code' => 'code', 'object_published' => 'published'],
            'vendor_catalog_category_pin' => ['object_code' => 'code'],
        ] as $tableName => $renames) {
            if (!$schema->hasTable($tableName)) {
                continue;
            }
            $table = $schema->getTable($tableName);
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
        $this->throwIrreversibleMigrationException('Current Vendoring Objecting names are canonical.');
    }
}
