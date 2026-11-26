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

## Security and browser follow-up

Baseline: e83a7db800596a0fc16004c05bc64f4a35d3f5b2.

The scoped Composer update retains Symfony 8.1, updates 16 packages and removes the previously reported advisories. The actual installed dependency graph passes the audit. Browser acceptance uses a verified matching ChromeDriver, a private loopback server, a fresh SQLite database and an ephemeral authenticated session. The complete PHPUnit run passes with 392 tests and 5711 assertions, no skips; five deprecations and one PHPUnit deprecation remain. The legacy Nelmio route import and the two documented runtime routes have been restored. Tenant identity is taken only from authorized scope.

## Historical reconciliation follow-up

The three archived references remain preserved. A refreshed private inventory identifies 17 unique historical commits. Normalized PHP token comparisons (ignoring whitespace/comments and using explicit namespace/class-name mappings) establish additional equivalence for 259 historical mapped files and three CRUD mapped files. This is evidence for those files, not a blanket proof of equivalence.

The historical snapshot has 1183 inventoried paths, including 90 exact blobs, 319 already absent deletions, 70 matching line deltas, 259 normalized matches, 361 mapped semantic differences, 29 unmapped paths and 55 retained historical deletions. The CRUD snapshot has 60 paths, including one exact blob, seven already absent deletions, one matching line delta, three normalized matches, 36 mapped semantic differences, two unmapped paths and ten retained historical deletions. The developer-experience reference has six inventory entries; its substantive monitoring regressions and transaction authorization improvements are ported, but the remaining source/test differences are not asserted equivalent.

Verified individual dispositions in this follow-up:
- Historical Core and CRUD create/update contracts are implemented by VendorCrudService using the repository contract, DTO validation, normalized brand names, events and owner assignment. Direct service regressions now check blank-name rejection before persistence or side effects.
- Historical null profile attachment fallback is implemented by VendorProfileAttachmentResolver, returning the empty projection.
- Historical payout statement alias is implemented by the current detail-provider subclass.
- Historical statement recipient default tenant is deliberately superseded by the trusted persisted ledger binding. Missing bindings fail closed.
- Historical deletion of current CI workflows, active canonical interfaces, events, repositories and regression tests is not applied merely because the older snapshot lacks them. Retain them until their individual contracts have been verified.

The private inventories retain individual paths and hashes. Unverified semantic differences remain open; do not mark reconciliation complete or merge solely on the strength of normalized comparisons.

## Remote CI blocker

GitHub Actions is enabled and the account is blocked at job initialization by a billing issue. The check annotation explicitly reports that the job was not started because the account is locked due to billing. Repository and available organization secret metadata do not contain GATING_READ_TOKEN. Neither billing nor credentials were modified. Remote acceptance and merge remain blocked until these conditions and the outstanding reconciliation are resolved.

## Historical semantic checkpoint — e3b6673

The pinned normalized inventories were reused. Pass 2 maps every formerly unmapped path or explicitly retires generated configuration residue. All 65 retained deletion candidates have a conservative disposition: retain four CI gates, 42 referenced contracts, ten published interfaces whose external consumers cannot be excluded, and nine current regression contracts. No deletion was performed. An additional 45 normalized executable-token and docblock matches and nine token matches requiring docblock review were identified. Normalization is comparison evidence, not proof of behavioral equivalence.

Pass 3 records 21 contract-specific reviews, with current target hashes and review boundaries. The restored contracts cover mandatory tenant metadata in five runtime projections, nested scopes, CLI output, direct statement export, PDF tenant provenance, recipient projections, and local sliding-window write enforcement. Rate-limit state is private to the runtime, serialized by a lock and replaced atomically; unavailable/corrupt state fails closed. The external backend remains injectable. Local state is not distributed and can reset with runtime cache cleanup. Multi-host deployments require an appropriate shared backend. The external integration builder's required tenant argument changes the public signature; host consumers must synchronize before integration.

Targeted regression: 116 tests, 795 assertions, zero skips, five deprecations and one PHPUnit deprecation. Includes real Chromium/Panther, authenticated kernel HTTP 429 and Retry-After, tenant-isolated statement delivery and PDF provenance. PHPStan: 832 files, zero errors; code style: 834 files, zero fixes; DI: 4 tests, 7 assertions, interface alias smoke PASS. No repeat of unchanged Windows, PostgreSQL, dependency audit or other previously accepted gates.

Private evidence in vendoring-history-disposition-a4c73c6a66d046f88d1ff3844f12fe41:
- historical-semantic-closure-pass3.json SHA256 53522d0e465288d3a65d429f2d1f410f0d51d79f4fde2ea705d48e082a0d67a2
- crud-semantic-closure-pass3.json SHA256 620a6e1b7a78a2f5d5e3510fcb5d7c2909a7e4eb3b84309ebca1695e57dfc030
- dx-semantic-closure-pass3.json SHA256 76d7e3fdefb94d47440b476a317a88dbf3545bd6c17adae9e69f33f39dba21dc

Residual: 314 historical, 37 CRUD and six DX pending semantic rows, plus nine docblock reviews. The 21 reviewed contract checkpoints do not assert whole-file equivalence. Historical tenant rollout fallback requires trusted identity design; transaction write authorization requires separate confirmation of vendor ownership rather than permission alone. Do not reintroduce untrusted query-derived identity or legacy unscoped ledger operations. Full semantic closure remains unproven.

Remote metadata at e3b6673856d3b3cbc24d3503758550fc03e09b27: all four workflow runs failed (Quality 45761823219, Security 45761823804, Runtime 45761823217, Docs 45761823305); all five checks report billing lock before execution. Actions enabled, allowed actions all. GATING_READ_TOKEN absent from both repository and available organization secret name inventories. No credentials read or modified. PR remains draft; no merge, canonical synchronization or deployment until complete acceptance.

## Transaction authorization and rollout checkpoint — 6e0a24c

Independent review found permission-only authorization in the JSON transaction service, unscoped by-ID lookup before authentication, and unauthenticated operator writes. All external transaction reads and writes now require an active persisted canonical Vendor and explicit bearer permission (read:transactions or write:transactions). A nullable, independently persisted owner_vendor_id foreign key is set for authenticated new transactions; external lookups require this association as well as the canonical identifier. An older row whose arbitrary vendor_id matches the caller's canonical ID remains invisible and unmodifiable. No historical owner is inferred. Existing business uniqueness constraints are retained; unverified historical rows can still block a colliding business key until explicit reconciliation. The forward PostgreSQL migration enforces owner identity and restricts deletion of referenced vendors. Internal legacy lifecycle calls without a trusted owner remain quarantined from the external surfaces. Host callers must synchronize canonical IDs and trusted bearer propagation; ordinary browser form navigation without authorization is denied.

Rollout restores vendor-to-tenant-to-global expansion and deterministic tenant-only cohort evaluation. HTTP requires read:rollout with a persisted financial binding; query identifiers only narrow that binding, and global manifest/rollback data are excluded from the vendor response. The local CLI accepts explicitly supplied operator scope. The standalone Symfony route is registered and tested. API version and actual rate-limit decision headers are restored; unmetered reads do not claim quota.

Targeted PHPUnit: 93 tests, 578 assertions, zero skips including actual Chromium authenticated operator form submission. Final affected transaction rerun after response metadata: 8 tests, 105 assertions, zero skips. Both report one PHPUnit deprecation. Ten smoke scripts pass, including the isolated synthetic and post-deploy verification pack (no deployment). PHPStan 833 files zero errors; CS fixer 835 files zero changes; DI alias smoke and 4 tests/7 assertions PASS; canary smoke PASS. Verified owned PostgreSQL cluster 7712329127051790454, socket 55432, database cmcp_test_vendoring_parity only: forward owner migration then fresh 13-migration schema parity (807 SQL queries, valid mapping, schema in sync, migrations up to date) PASS. PostgreSQL sandbox contained zero pre-existing transaction rows; a nonzero unowned historical row is covered by the SQLite kernel test. No production data/schema or canonical checkout changed.

Pass 4 reused the exact Pass 3 SHA256-pinned inventories and resolved PHP ASTs with explicit FQCN mappings; Pass 5 individually reviewed ten docblock differences (formatting, global exception names, ledger entity rename). 14 additional contract-specific rows were dispositioned; 342 semantic rows remain (302 historical, 36 CRUD, 4 DX), zero pending docblock reviews. No blanket behavioral equivalence is asserted. Prior retained deletions and archive refs are unchanged. Private evidence:
- historical-semantic-closure-pass5.json SHA256 a93c043a410e9cc4e607825897723bdea20ed4ac9fa36faa1ccfb182cdd71ae3
- crud-semantic-closure-pass5.json SHA256 c9fded979e504097b6e6b0bf9190601ea3cd54683786f081d05a1b50b156fd56
- dx-semantic-closure-pass5.json SHA256 624e09ad92d8f011f1205fb66361bdc3a6afd9d66d9dd599f8783e00a1187a4a

Do not mark ready or merge until remaining semantic reviews and all remote gates pass. The optional legacy standalone Playwright script is not the authenticated acceptance gate; the actual Chromium/Panther tests replace its evidence, and its stale fixture requires synchronization if used. No dependency audit repeated, since composer manifests/lock are unchanged.
