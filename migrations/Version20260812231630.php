<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

final class Version20260812231630 extends AbstractMigration
{
    private const PROJECTION = __DIR__ . '/SchemaPg/20260812_231600_vendoring_entity_projection.sql';

    public function getDescription(): string
    {
        return 'Adopt or create the full Vendoring PostgreSQL schema projected from current Entity/Objecting metadata';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Vendoring production schema requires PostgreSQL.');

        $sql = $this->projectionSql();
        preg_match_all('/CREATE TABLE ([a-zA-Z0-9_]+)/', $sql, $matches);
        $expectedTables = array_values(array_unique($matches[1] ?? []));
        $this->abortIf([] === $expectedTables, 'Vendoring schema projection does not contain any CREATE TABLE statements.');

        $present = array_values(array_filter($expectedTables, static fn (string $table): bool => $schema->hasTable($table)));

        if ([] === $present) {
            foreach ($this->projectionStatements($sql) as $statement) {
                $this->addSql($statement);
            }
        } else {
            $missing = array_values(array_diff($expectedTables, $present));
            $this->abortIf([] !== $missing, sprintf('Refusing partial Vendoring baseline adoption. Missing tables: %s', implode(', ', $missing)));
            $this->assertRequiredColumns($schema);
        }

        if ($schema->hasTable('vendor_payout_account') && !$schema->getTable('vendor_payout_account')->hasColumn('account_id')) {
            $count = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM vendor_payout_account');
            $this->abortIf($count > 0, 'vendor_payout_account contains rows but has no account_id column; explicit data backfill is required before adoption.');
            $this->addSql('ALTER TABLE vendor_payout_account ADD account_id VARCHAR(64) NOT NULL');
        }

        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_vendor_payout_business_id ON vendor_payout (payout_id)');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_vendor_payout_account_business_id ON vendor_payout_account (account_id)');
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration('Vendoring baseline adoption is intentionally irreversible; destructive rollback must use an explicit recovery migration.');
    }

    private function projectionSql(): string
    {
        $sql = @file_get_contents(self::PROJECTION);
        $this->abortIf(false === $sql || '' === trim($sql), 'Vendoring schema projection file is missing or empty.');

        return $sql;
    }

    /** @return list<string> */
    private function projectionStatements(string $sql): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: []),
            static fn (string $statement): bool => '' !== $statement,
        ));
    }

    private function assertRequiredColumns(Schema $schema): void
    {
        foreach ([
            'vendor' => ['id', 'brand_name', 'object_uuid', 'object_slug', 'object_created_at', 'object_status'],
            'vendor_transaction' => ['id', 'vendor_id', 'order_id', 'amount', 'status', 'created_at', 'object_uuid', 'object_slug'],
            'vendor_payout' => ['id', 'payout_id', 'vendor_id', 'currency', 'gross_cents', 'fee_cents', 'net_cents', 'status', 'object_uuid', 'object_slug'],
            'vendor_payout_account' => ['id', 'tenant_id', 'vendor_id', 'provider', 'account_ref', 'currency', 'object_uuid', 'object_slug'],
        ] as $tableName => $columns) {
            $table = $schema->getTable($tableName);
            foreach ($columns as $column) {
                $this->abortIf(!$table->hasColumn($column), sprintf('Existing %s table is missing required column "%s".', $tableName, $column));
            }
        }
    }
}
