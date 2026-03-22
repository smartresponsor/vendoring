# Vendoring wave31 report

Active base: wave30 cumulative snapshot.

## Change
- Repaired semantic payload loss in `src/Service/Vendor/Payout/PayoutProviderBridge.php`.
- `transfer()` accepted `tenantId`, `vendorId`, `provider`, `accountRef`, `amount`, `currency`, but previously returned only `ok`, `ref`, and `error`.
- The bridge result now preserves the accepted transfer scope and transfer inputs in the returned payload.

## Why
- This is a live service-layer semantic mismatch: method parameters were accepted but silently discarded from the bridge result.
- For a payout bridge, dropping transfer scope makes downstream auditing and debugging weaker even in stub mode.
- The change is narrow and does not alter routing, DTOs, or controller contracts.
