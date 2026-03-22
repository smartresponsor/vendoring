# Vendoring wave 50 — entity autoload foundation

## What was fixed
- Added missing core `src/Entity/Vendor/*` classes that were referenced across services, events, repository contracts, and commands but absent from the codebase.
- Added focused entity tests covering vendor lifecycle, api key permission parsing, payout status transitions, passport verification, and transaction status mutation.
- Added entity smoke check and registered `test:entity` in Composer scripts and quality flow.

## Why this wave matters
Before this wave, multiple service and repository slices referenced non-existent vendor entities, making the component structurally cleaner than before but still autoload-incomplete in a large part of the domain surface.

This wave does not claim full Doctrine mapping completeness. It restores the missing core domain classes so the service/event/command surface can evolve on a real entity layer instead of phantom imports.
