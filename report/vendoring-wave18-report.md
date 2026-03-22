# Vendoring wave18 report

Active base: wave17 cumulative snapshot.

## Changes

1. Repaired ledger repository contract gap used by `PayoutService`:
   - added `balancesForVendor(string $vendorId): array` to `LedgerEntryRepositoryInterface`
   - implemented `balancesForVendor()` in `LedgerEntryRepository` as grouped currency balance read-model returning objects with `currency` and `balanceCents`

2. Repaired payout item entity/repository shape mismatch:
   - aligned `App\Entity\Vendor\Payout\PayoutItem` with actual repository persistence/read model (`payoutId`, `entryId`, `amountCents`)

3. Removed unused import from `PayoutService`.

## Rationale

These are factual anomalies inside the live Vendor service/repository layer:
- `PayoutService` already called `balancesForVendor()` on the ledger repository contract, but the method did not exist in either the interface or implementation.
- `PayoutRepository` persisted and hydrated `PayoutItem` as `payout_id` / `entry_id` / `amount_cents`, while the entity constructor still described a different batch/reference/currency model.

## Verification

- `php tools/vendoring-structure-scan.php --strict` → PASS
- `php tools/vendoring-psr4-scan.php --strict` → PASS
- `php tools/vendoring-missing-class-scan-v3.php --strict --limit=500` → PASS
