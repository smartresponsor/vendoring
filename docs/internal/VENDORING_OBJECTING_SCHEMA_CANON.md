# Vendoring Objecting Schema Canon

## Ownership

Objecting owns reusable object-system fields.
Vendoring owns Vendor business entities and business fields only.
App migrations are runtime projections of current entity mapping, not schema authority.

## Source of truth

Entity mapping is the source of truth for database design.
Objecting embeddables are the source of truth for object-system columns.
Consumer migrations must mirror entity mappings and Objecting embeddables.
A migration column, index, or constraint without mapping symmetry is non-canonical drift.

## Objecting field projection

Vendoring entities inherit generic system fields from VendorAbstractEntity.
VendorAbstractEntity composes Objecting identity, audit, and state traits.
Objecting column names are canonical: object_uuid, object_slug, object_created_at, object_modified_at, object_created_by, object_modified_by, object_active, object_enabled, and object_status.

Vendoring must not duplicate lifecycle audit fields such as local createdAt or created_at.
Payout creation time is exposed through Objecting audit accessors such as getCreatedAt().

## Migration policy

Migrations may add or backfill Objecting columns only as projection of Objecting embeddables.
Business indexes and unique constraints stay in Vendoring entity mapping when they support read-model or business invariants.
Historical migrations are acceptable only after production has executed them.
Before production, prefer entity-first cleanup and regenerated clean schema over migration archaeology.

## Production target

The target before production is zero migration drift.
Every database column, index, and constraint must be mirrored by an entity mapping or Objecting embeddable.
App must not become the design authority for Vendoring schema.
