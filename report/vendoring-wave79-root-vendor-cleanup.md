# Vendoring Wave 79 - Root vendor directory cleanup

## Summary
- removed persistent `vendor/` directory from cumulative source snapshot
- extended canonical root structure contract to forbid committed root-level `vendor/`
- extended root structure smoke and composer quality surface accordingly

## Rationale
Installed Composer dependencies are runtime/build artifacts and must not live inside the canonical cumulative repository snapshot.
