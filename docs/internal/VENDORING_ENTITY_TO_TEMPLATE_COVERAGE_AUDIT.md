# Vendoring Wave 14 — Entity-to-template coverage audit

## Scope

- Baseline current slice: `www-clean-20260611-151149.zip`.
- Stack baseline: Wave 13 Cruding stack alignment.
- Entity graph source: user-provided `Vendoring/Entity` relation report.
- This wave is audit-only: no persistence reconstruction, no controller introduction, no delete-list.

## Goal

Ensure no `Vendor*Entity` business surface remains hidden from Cruding/Viewing because of missing or incorrectly named entrypoints.

Required chain for a routable Vendor business object:

```text
Vendor*Entity
  -> Vendor*Repository / Vendor*RepositoryInterface
  -> Vendor*Service route entrypoint
  -> Vendor*Type when mutation/form operation exists
  -> Viewing payload contract
  -> explicit template candidate or documented Viewing fallback
```

## Summary

- `sourceCurrentSlice`: `www-clean-20260611-151149.zip`
- `entityGraphSource`: `Pasted text(753).txt`
- `entityCount`: `46`
- `routeEntryCount`: `81`
- `routeStemCount`: `10`
- `entityPhysicalCount`: `0`
- `repositoryPhysicalCount`: `0`
- `entitiesWithRoutes`: `9`
- `entitiesWithoutRoutes`: `36`
- `entitiesWithDirectTemplate`: `1`
- `orphanRouteStems`: `['VendorOnboarding', 'VendorProduct', 'VendorRating']`
- `orphanRouteStemCount`: `3`

## Key finding

Wave 13 fixed route-target existence and Cruding entrypoint naming, but it did not restore the entity/repository/template chain. Therefore it is not yet enough to guarantee that all Vendor business logic is reachable from entity to template.

The route surface currently covers only the route-map subjects that exist in `config/platform/routes/**`. The user-provided entity graph contains many more `Vendor*Entity` candidates than the current route map exposes.

## Covered by route surface

| Entity | Route keys | Service files | Form type files | Template | Status |
|---|---:|---:|---:|---:|---|
| `VendorAttachmentEntity` | 40 | 26 | 18 | no | `route_exists_but_template_not_declared` |
| `VendorCategoryEntity` | 2 | 2 | 2 | no | `route_exists_but_template_not_declared` |
| `VendorCommissionEntity` | 3 | 2 | 2 | no | `route_exists_but_template_not_declared` |
| `VendorCommissionHistoryEntity` | 3 | 2 | 2 | no | `route_exists_but_template_not_declared` |
| `VendorDocumentAttachmentEntity` | 20 | 13 | 9 | no | `route_exists_but_template_not_declared` |
| `VendorDocumentEntity` | 2 | 2 | 2 | no | `route_exists_but_template_not_declared` |
| `VendorMediaAttachmentEntity` | 20 | 13 | 9 | no | `route_exists_but_template_not_declared` |
| `VendorPayoutEntity` | 4 | 3 | 3 | no | `route_exists_but_template_not_declared` |
| `VendorPayoutItemEntity` | 4 | 3 | 3 | no | `route_exists_but_template_not_declared` |

## Entity surfaces missing from Cruding route map

These are the high-risk objects for your stated criterion: business entities can exist in the graph but Cruding cannot discover them through route-map naming.

| Entity | Candidate stem | Current risk |
|---|---|---|
| `VendorAddressEntity` | `VendorAddress` | `business_entity_without_cruding_surface` |
| `VendorAnalyticsEntity` | `VendorAnalytics` | `business_entity_without_cruding_surface` |
| `VendorApiKeyEntity` | `VendorApiKey` | `business_entity_without_cruding_surface` |
| `VendorBillingEntity` | `VendorBilling` | `business_entity_without_cruding_surface` |
| `VendorCatalogCategoryBannerEntity` | `VendorCatalogCategoryBanner` | `business_entity_without_cruding_surface` |
| `VendorCatalogCategoryChangeRequestEntity` | `VendorCatalogCategoryChangeRequest` | `business_entity_without_cruding_surface` |
| `VendorCatalogCategoryHtmlBlockEntity` | `VendorCatalogCategoryHtmlBlock` | `business_entity_without_cruding_surface` |
| `VendorCatalogCategoryPinEntity` | `VendorCatalogCategoryPin` | `business_entity_without_cruding_surface` |
| `VendorCatalogReviewAssignmentEntity` | `VendorCatalogReviewAssignment` | `business_entity_without_cruding_surface` |
| `VendorChannelEntity` | `VendorChannel` | `business_entity_without_cruding_surface` |
| `VendorCodeStorageEntity` | `VendorCodeStorage` | `business_entity_without_cruding_surface` |
| `VendorConversationEntity` | `VendorConversation` | `business_entity_without_cruding_surface` |
| `VendorConversationMessageEntity` | `VendorConversationMessage` | `business_entity_without_cruding_surface` |
| `VendorCustomerOrderEntity` | `VendorCustomerOrder` | `business_entity_without_cruding_surface` |
| `VendorFavouriteEntity` | `VendorFavourite` | `business_entity_without_cruding_surface` |
| `VendorGroupEntity` | `VendorGroup` | `business_entity_without_cruding_surface` |
| `VendorIbanEntity` | `VendorIban` | `business_entity_without_cruding_surface` |
| `VendorLedgerBindingEntity` | `VendorLedgerBinding` | `business_entity_without_cruding_surface` |
| `VendorLedgerEntryEntity` | `VendorLedgerEntry` | `business_entity_without_cruding_surface` |
| `VendorLogEntity` | `VendorLog` | `business_entity_without_cruding_surface` |
| `VendorMediaEntity` | `VendorMedia` | `business_entity_without_cruding_surface` |
| `VendorPassportEntity` | `VendorPassport` | `business_entity_without_cruding_surface` |
| `VendorPaymentEntity` | `VendorPayment` | `business_entity_without_cruding_surface` |
| `VendorPayoutAccountEntity` | `VendorPayoutAccount` | `business_entity_without_cruding_surface` |
| `VendorProfileAvatarEntity` | `VendorProfileAvatar` | `business_entity_without_cruding_surface` |
| `VendorProfileCoverEntity` | `VendorProfileCover` | `business_entity_without_cruding_surface` |
| `VendorProfileEntity` | `VendorProfile` | `business_entity_without_cruding_surface` |
| `VendorRememberMeTokenEntity` | `VendorRememberMeToken` | `business_entity_without_cruding_surface` |
| `VendorSecurityEntity` | `VendorSecurity` | `business_entity_without_cruding_surface` |
| `VendorServiceEntity` | `VendorService` | `business_entity_without_cruding_surface` |
| `VendorShipmentEntity` | `VendorShipment` | `business_entity_without_cruding_surface` |
| `VendorTransactionEntity` | `VendorTransaction` | `business_entity_without_cruding_surface` |
| `VendorTranslationEntity` | `VendorTranslation` | `business_entity_without_cruding_surface` |
| `VendorUserAssignmentEntity` | `VendorUserAssignment` | `business_entity_without_cruding_surface` |
| `VendorWishlistEntity` | `VendorWishlist` | `business_entity_without_cruding_surface` |
| `VendorWishlistItemEntity` | `VendorWishlistItem` | `business_entity_without_cruding_surface` |

## Route subjects without matching entity in the provided graph

These route groups may be workflow/computed surfaces, or they may reveal missing entity names in the graph.

| Route stem | Route keys | Decision |
|---|---:|---|
| `VendorOnboarding` | 6 | classify as workflow/computed, or add matching `Vendor*Entity` if it is persistent business data |
| `VendorProduct` | 2 | classify as workflow/computed, or add matching `Vendor*Entity` if it is persistent business data |
| `VendorRating` | 2 | classify as workflow/computed, or add matching `Vendor*Entity` if it is persistent business data |

## Template coverage

Current direct Twig templates under `templates/`:

- `templates/_macros/crud.html.twig`
- `templates/base.html.twig`
- `templates/ops/vendor_transactions/index.html.twig`

Result: direct Vendor route templates are not declared for the route-map business surfaces. Viewing fallback may render neutral payloads, but that is not sufficient for a 100% entity-to-template guarantee.

## Required next repair order

1. Define the canonical Vendor persistence surface: `VendorEntity`, `VendorRepository`, and first-rank child entities only.
2. Decide which entity graph nodes are embedded/profile records versus independent Cruding resources.
3. For every independent routable entity, add/check route-map subject, `Vendor*Service`, mutation `Vendor*Type`, repository contract, and template candidate.
4. For embedded/non-routable records, explicitly mark them `embedded_or_internal_record` in the matrix so they are not mistaken for hidden business logic.
5. Add a CI/audit check that fails when a `Vendor*Entity` has no declared route/template policy.

## No-delete statement

Wave 14 deletes no files. This is a coverage/audit report update only.
