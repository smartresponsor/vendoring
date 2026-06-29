# Vendoring Objecting Entity-First Canon

Vendoring owns Vendor business entities and Vendor business fields.
Objecting owns reusable object-system fields and their Doctrine column vocabulary.

## Ownership boundary

Vendoring must not duplicate fields that are already modeled by Objecting system-field packs.

Objecting-owned fields include:

- `object_uuid`
- `object_slug`
- `object_created_at`
- `object_modified_at`
- `object_created_by`
- `object_modified_by`
- `object_active`
- `object_enabled`
- `object_status`

Vendoring entities should consume these fields through Objecting interfaces and embeddable traits:

- `ObjectIdentifiedInterface` with `ObjectIdentityEmbeddableTrait`
- `ObjectAuditedInterface` with `ObjectAuditEmbeddableTrait`
- `ObjectStatefulInterface` with `ObjectStateEmbeddableTrait`

The common Vendoring composition point is `VendorAbstractEntity`.

## Entity-first schema rule

Entity mapping is the source of truth for database design.

Doctrine migrations are allowed only as runtime materialization, data backfill, or alignment projection of the current entity mappings and Objecting embeddables.

A migration is non-canonical when it introduces a column, index, foreign key, or constraint that is not symmetrical with either:

1. a Vendoring business entity mapping; or
2. an Objecting embeddable consumed by the entity.

Before production, prefer a clean entity-first schema over migration archaeology. Historical migration chains are not design authority.

## Lifecycle timestamp rule

Generic lifecycle timestamps belong to Objecting audit, not to Vendoring local fields.

For payout entities, do not create local `createdAt` or `created_at` fields when the meaning is lifecycle creation time.

Use:

- column: `object_created_at`
- PHP surface: `getCreatedAt()`
- initialization: Objecting audit embeddable initialization through `VendorAbstractEntity`

