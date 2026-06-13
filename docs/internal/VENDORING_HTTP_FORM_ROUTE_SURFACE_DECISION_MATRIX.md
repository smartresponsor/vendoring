# Vendoring Wave 12B — HTTP/Form Route Surface Decision Matrix

## Purpose
Wave 12A made route-map targets physically resolvable by adding HTTP service and form skeletons. Wave 12B classifies those targets so the component does not confuse resolvability with real business readiness.

## Non-goals
- No controllers are added.
- No Doctrine entities/repositories are restored.
- No quarantined persistence services from Wave 11 are deleted or re-enabled.
- No files are deleted in this wave.

## Counts
- Route-map entries: 81
- Unique HTTP service targets: 54
- Unique form type targets: 42
- `business_operation_domain_blocked`: 21
- `crud_protocol_persistence_blocked`: 51
- `real_read_candidate`: 9

## Decision classes
### `real_read_candidate`
Read-only route targets such as index/show. These are the only safe candidates for Wave 12C implementation without bringing back write-side persistence.

### `crud_protocol_persistence_blocked`
Classic CRUD/write/form routes. They must stay skeleton/protocol-only until a real Vendor persistence and command model exists.

### `attachment_surface_persistence_blocked`
Attachment/document/media routes. The grammar is useful, but the subject provider, attachment storage, and persistence contracts are not restored.

### `business_operation_domain_blocked`
Category, commission, onboarding, payout, product, rating, and document verification operations. These require domain services, policies, events, and persistence that are still quarantined or missing.

## Canonical next step
Proceed to **Vendoring Wave 12C** only for `real_read_candidate` routes. Do not implement writes, payouts, onboarding, commissions, or attachment mutation until the persistence model is deliberately rebuilt.

## Audit artifact
See `delivery/audit/vendoring-wave12b-http-form-decision-matrix.json` for the full route-by-route matrix.
