# Historical data closure — Vendoring rows 32, 34, 35, 64

The pinned historical source (ea60599dfeb69feae1c8d12322b0a094115afd13) persisted arbitrary JSON and nullable code. Its three catalog constructor calls passed typed positional arguments incompatible with that historical entity constructor. No authoritative JSON producer or ownership mapping was established for those old rows. Do not guess keys, publication state, vendor identity or category from code, slug or coincident IDs.

The explicit PostgreSQL repair in `migrations/repair/vendor_legacy_data.sql` is an offline pre-adoption operation, **not** an automatically executed Doctrine migration. Existing migration 20260819224500 rejects nonempty unowned service data; run the repair before that migration if legacy schema is present. It also recognizes already-normalized catalog schemas and adopted service records without modifying them. Partial/mixed layouts fail closed.

Before any real invocation: verified backup, exclusive maintenance window, schema review, and independently approved per-row decisions based on authoritative source records. Use a single psql session with ON_ERROR_STOP=1. Create a session-local mapping table:

```sql
CREATE TEMP TABLE vendoring_legacy_mapping (
    source_table text, source_id integer, source_sha256 text,
    evidence text, target jsonb
) ON COMMIT PRESERVE ROWS;
```

For each legacy table, obtain its exact source fingerprint before any alteration:

```sql
SELECT id, encode(sha256(convert_to(
    jsonb_build_array(to_jsonb(s), s.payload::text)::text, 'UTF8'
)), 'hex') AS source_sha256
FROM public.vendor_catalog_category_banner s ORDER BY id;
```

Use the same expression for the other three legacy tables. Do not export raw payloads into logs. Populate the temp table with exactly one mapping per legacy row and a nonempty evidence reference. Target JSON keys are exact:

- Banner: category_id, title, content, published, published_at.
- HTML block: category_id, html, published, published_at.
- Pin: category_id, record_id, position.
- Service: vendor_id, category_id (existing code and payload are retained verbatim).

Publication timestamp must be explicitly decided: local database timestamp without timezone, ISO seconds, or JSON null for unpublished. The operator must verify real category/record identity and publication authorization; the repair cannot establish these from arbitrary historical JSON. The owner must exist in the canonical Vendor table; duplicate service owner/category combinations fail the unique constraint. Unknown, stale, missing or ambiguous mappings abort the transaction. Existing source columns, metadata and raw JSON text are preserved in the access-restricted separate schema `vendoring_legacy_preservation.row_archive` before transformation. Retain that archive and backup; do not drop it during schema cleanup. Doctrine schema operations must remain scoped to the application schema; verify schema filtering before use.

Run the repair with `\i migrations/repair/vendor_legacy_data.sql` in the same session. It takes exclusive locks, verifies complete coverage and fingerprints, applies typed values and constraints, and commits as a unit. A failure rolls back DDL, DML and archive creation. No automatic reverse migration is provided; restore the independently verified backup for rollback after commit. Never use schema:update --force to convert a legacy layout.

Targeted PostgreSQL acceptance on a newly owned isolated cluster: nonempty fixtures for all four legacy tables, exact raw JSON preservation (including duplicate keys), typed targets, owner foreign key and owner/category uniqueness, canonical no-op rerun, and atomic rollback for missing, stale, duplicate, invalid-owner, invalid-pin, invalid-publication, mixed-schema and colliding mappings. Nine scenarios passed. Reproduce with `python3 tests/bin/vendor-legacy-data-repair-postgres.py --socket <owned-pass9-socket> --cluster <verified-cluster-id>` on a dedicated test cluster only.

This establishes an explicit migration contract, not proof that real legacy rows exist or that their business mapping has been approved. The four semantic dispositions remain pending actual authoritative per-row mapping or a verified absence of legacy rows. Production data and canonical Host are outside the acceptance scope.
