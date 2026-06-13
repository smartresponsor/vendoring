# Vendoring Wave W: TF-IDF acronym cleanup audit

## Scope

This wave is intentionally narrow. It normalizes the remaining search-service acronym naming after the earlier PDF acronym cleanup.

## Finding

`src/Service/Search` and `src/ServiceInterface/Search` still exposed `VendorTfidfSearchService*`. The class was technically valid, but the acronym was not segmented consistently with the repository current class-name style after `PDF` was normalized to `Pdf`.

## Change

- `VendorTfidfSearchService` -> `VendorTfIdfSearchService`
- `VendorTfidfSearchServiceInterface` -> `VendorTfIdfSearchServiceInterface`
- Updated the explicit DI alias in `config/component/services.yaml`.

## Boundary

No runtime behavior changed. The TF-IDF implementation, tokenization, scoring, and public service contract methods remain unchanged.

## Validation expectation

Run:

```bash
composer dump-autoload
php bin/console lint:container
php tests/bin/interface-alias-smoke.php
```
