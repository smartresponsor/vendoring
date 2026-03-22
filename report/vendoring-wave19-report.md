# Vendoring wave19 report

Active base: cumulative snapshot wave18.

## Scope
Step-by-step code wave inside `src/Service/Vendor` and adjacent repository contracts.

## Changes
- Removed unused repository dependency from `VendorService`.
- Removed unused repository dependency from `VendorDocumentService`.
- Removed unused repository dependency from `VendorPassportService`.
- Removed unused attachment repository dependency from `VendorMediaService`.
- Removed unused payout repository dependency from `Metric/VendorMetricService`.

## Why
These dependencies were injected but never read in method bodies. They created false container requirements and unnecessary coupling without adding business behavior.

## Safety
- No method signatures on public service interfaces were changed.
- No repository contracts were changed.
- No runtime behavior changed except constructor wiring becoming narrower and more honest.
