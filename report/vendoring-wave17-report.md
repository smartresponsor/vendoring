# Vendoring wave17 report

Active base: cumulative snapshot wave16.

## Scope
Targeted factual audit and repair inside:
- `src/Service/Vendor`
- `src/Repository/Vendor`
- matching interface roots under `src/ServiceInterface/Vendor` and `src/RepositoryInterface/Vendor`

## Findings repaired
1. Missing service interface file for `App\Service\Vendor\CrmService`.
2. Missing service interface file for `App\Service\Vendor\Payout\PayoutService`.
3. Empty repository interface `App\RepositoryInterface\Vendor\Ledger\LedgerEntryRepositoryInterface`.
4. Empty repository interface `App\RepositoryInterface\Vendor\Payout\PayoutRepositoryInterface`.
5. Concrete repository dependency in `PayoutService` was narrowed to `LedgerEntryRepositoryInterface`.

## Notes
This wave intentionally does not fabricate missing entity trees such as `App\Entity\Vendor\Vendor` or cross-domain services such as `App\Service\Ledger\LedgerService`; those require a broader repository decision and are outside a safe step-by-step repair.
