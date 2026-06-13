# Vendoring Wave 16 — Service Registration / Quarantine Reconciliation

Wave 16 reconciles Symfony service registration with the physically restored Vendoring persistence layer.

## Activated

- canonical `App\Vendoring\Service\Http\` resource;
- canonical `App\Vendoring\Form\` resource;
- persistence-bound business services previously excluded by Wave 11;
- 46 `Vendor*RepositoryInterface -> Vendor*Repository` bindings;
- safe default `VendorProfileAttachmentResolverServiceInterface` binding to `NullVendorProfileAttachmentResolverService`.

## Still intentionally isolated

- Doctrine entities and interfaces remain excluded from service discovery;
- `Service/Profile/Bridge/` remains optional;
- `VendorChainedProfileAttachmentResolverService` is registered only by `optional/attaching_profile_bridge.yaml`;
- fixtures, DTOs, value objects and support classes remain outside service discovery.

## Important

Wave 11 quarantine was valid for an incomplete slice where entities and repositories were absent. In the full component slice, those dependencies exist, so keeping the old exclusions made business services invisible to Symfony and Cruding.
