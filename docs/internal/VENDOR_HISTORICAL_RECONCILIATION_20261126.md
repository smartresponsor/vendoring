# Vendoring PR 41 reconciliation checkpoint

Baseline: c93e9722775949d069d1c5a19ab5ca8eaee0061e.
Scope: isolated candidate; no production deployment or canonical worktree mutation.

## Canonical Ubuntu delta disposition

Paths below use the original canonical names where the candidate has moved classes.

| Path or group | Disposition |
|---|---|
| config/component/services.yaml | Preserve new DI layout, restore actual logger and optional host channel. |
| src/DataFixtures/VendorAccessBootstrapFixture.php | Port authoritative vendor/user assignment lookup with explicit persisted identity. |
| src/Entity/VendorTransactionEntity.php; src/Repository/Vendor/VendorTransactionRepository.php | Keep newer explicit transaction status/created_at mapping, repository ordering and prior PostgreSQL parity. |
| src/Repository/Vendor/VendorRepository.php; src/RepositoryInterface/Vendor/VendorRepositoryInterface.php | Retain expanded repository contract, ownership counts and access bootstrap query. |
| src/Service/Attachment/Bridge/VendorAttachingOwnerPurgeService.php | Retain optional standalone bridge rather than impose undeclared Attaching dependency. |
| src/Service/Catalog/VendorCatalogMerchService.php | Retain repository-backed service; repair actual embedded code mapping and verify with Doctrine. |
| src/Service/Statement/ApiPayoutStatementDetailSurfaceProvider.php; src/Service/Statement/ApiPayoutStatementExportSurfaceProvider.php | Restore tenant metadata in moved providers without reverting authorization; preserve opaque identifiers. |
| src/Service/Vendor/VendorAbstractCrudRouteService.php | Retain renamed equivalent abstract contract. |
| src/Service/Vendor/VendorCreateService.php; VendorIndexService.php; VendorDeleteService.php; VendorUpdateService.php | Retain newer repository-backed operations, attachment purge and blank-input validation. |
| src/Service/Vendor/VendorEditService.php; VendorNewService.php; VendorShowService.php; VendorHttpRouteResponseService.php | Retain normalized equivalent implementations and current route response contract. |
| tests/Support/Transaction/DoctrineBackedVendorTransactionRepository.php; tests/Unit/Entity/VendorTransactionDoctrineMappingTest.php; tests/Unit/Infrastructure/VendorTransactionMappingParityTest.php | Retain tests corresponding to newer transaction mapping. |
| tests/Unit/Ledger/VendorDoubleEntryServiceTest.php | Restore strict UUIDv7 assertions. |
| tests/Unit/Service/CatalogMerchServiceTest.php | Add real repository/ORM isolation regression. |
| tests/Unit/Service/VendorRouteServicesTest.php | Retain repository-contract and stronger validation regressions. |

Deletion review: retain the active FakeStatementExporterPdf and StatementExporterPdfTest contracts; historical deletion does not justify dropping current PDF coverage. The two tracked payout entity .bak snapshots are historical residue; retain pending explicit repository cleanup, with preservation already verified. Generated reference configuration is not a source merge candidate.

## Historical references

Preserved checkpoints: ea60599dfeb69feae1c8d12322b0a094115afd13, 1cd0398cfc060574aafec346c84b9f7f193d1116, and b77a35f70185bacc94770a1a0e3332cf9da9164c. Developer-experience monitoring timestamp, BOM and streaming semantics are ported with 25 historical regression cases; transaction authentication ordering and rate-limit key handling are improved. Historical branches containing obsolete insecure tenant defaults or query-derived ownership must not overwrite the current binding contract.

The broader historical inventory is not a proof of full semantic equivalence. Remaining mapped/unmapped historical deltas require individual disposition; stash contents remain preserved privately. Do not merge on the basis of this checkpoint alone.

## Acceptance boundaries

Local readiness, transaction integration, unit/runtime and static checks pass with browser skips explicitly outstanding. CI dependency checkouts are pinned to the eight Composer path package revisions, including private Gating (requires restricted read credential). Composer audit advisories remain outstanding. Exact historical Gitleaks false-positive fingerprints were verified individually; no new secret findings are waived. Remote GitHub checks and full browser acceptance remain required before merge.
