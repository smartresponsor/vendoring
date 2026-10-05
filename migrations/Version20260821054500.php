<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

final class Version20260821054500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adopt canonical Retailing type paths for active vendor marketplace capabilities.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Vendoring production schema requires PostgreSQL.');
        $this->abortIf(!$schema->hasTable('vendor_service'), 'vendor_service table is required before Retailing capability adoption.');
        $this->abortIf(!$schema->hasTable('retail'), 'retail table is required before Retailing capability adoption.');

        $unresolvable = (int) $this->connection->fetchOne(<<<'SQL'
SELECT COUNT(*)
FROM (
    SELECT vendor_service.id
    FROM vendor_service
    LEFT JOIN LATERAL json_array_elements_text(
        CASE
            WHEN json_typeof(vendor_service.payload->'offeringIds') = 'array' THEN vendor_service.payload->'offeringIds'
            ELSE '[]'::json
        END
    ) offering(id_text) ON TRUE
    LEFT JOIN retail ON offering.id_text ~ '^[0-9]+$' AND retail.id = offering.id_text::int
    WHERE vendor_service.payload->>'source' = 'retailing'
      AND vendor_service.object_status = 'active'
    GROUP BY vendor_service.id
    HAVING COUNT(offering.id_text) = 0
        OR COUNT(retail.id) <> COUNT(offering.id_text)
        OR COUNT(DISTINCT retail.type_path) <> 1
        OR COUNT(*) FILTER (WHERE retail.type_path IS NULL OR btrim(retail.type_path) = '') > 0
) invalid_capability
SQL);
        $this->abortIf($unresolvable > 0, 'Active Retailing vendor capabilities must resolve to exactly one canonical type path.');

        $collisionCount = (int) $this->connection->fetchOne(<<<'SQL'
SELECT COUNT(*)
FROM (
    SELECT vendor_service.id, vendor_service.vendor_id, MIN(retail.type_path) AS type_path
    FROM vendor_service
    JOIN LATERAL json_array_elements_text(vendor_service.payload->'offeringIds') offering(id_text) ON TRUE
    JOIN retail ON retail.id = offering.id_text::int
    WHERE vendor_service.payload->>'source' = 'retailing'
      AND vendor_service.object_status = 'active'
    GROUP BY vendor_service.id, vendor_service.vendor_id
) target
JOIN vendor_service existing
  ON existing.vendor_id = target.vendor_id
 AND existing.category_id = target.type_path
 AND existing.id <> target.id
SQL);
        $this->abortIf($collisionCount > 0, 'Canonical Retailing capability adoption would collide with an existing vendor capability key.');

        $column = $schema->getTable('vendor_service')->getColumn('category_id');
        if (($column->getLength() ?? 0) < 255) {
            $this->addSql('ALTER TABLE vendor_service ALTER COLUMN category_id TYPE VARCHAR(255)');
        }

        $this->addSql(<<<'SQL'
UPDATE vendor_service
SET category_id = target.type_path,
    code = 'retailing:' || target.type_path,
    payload = jsonb_set(
        jsonb_set(vendor_service.payload::jsonb, '{catalogCode}', to_jsonb('retailing'::text), TRUE),
        '{typePath}',
        to_jsonb(target.type_path),
        TRUE
    )::json
FROM (
    SELECT source.id, MIN(retail.type_path) AS type_path
    FROM vendor_service source
    JOIN LATERAL json_array_elements_text(source.payload->'offeringIds') offering(id_text) ON TRUE
    JOIN retail ON retail.id = offering.id_text::int
    WHERE source.payload->>'source' = 'retailing'
      AND source.object_status = 'active'
    GROUP BY source.id
    HAVING COUNT(DISTINCT retail.type_path) = 1
) target
WHERE vendor_service.id = target.id
SQL);
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration('Canonical Retailing vendor capability keys are durable business classification and intentionally irreversible.');
    }
}
