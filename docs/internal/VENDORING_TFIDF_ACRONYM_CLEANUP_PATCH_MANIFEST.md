# Vendoring Wave W: TF-IDF acronym cleanup patch manifest

## Added / updated

- `src/Service/Search/VendorTfIdfSearchService.php`
- `src/ServiceInterface/Search/VendorTfIdfSearchServiceInterface.php`
- `config/component/services.yaml`
- `docs/internal/VENDORING_TFIDF_ACRONYM_CLEANUP_AUDIT.md`
- `docs/internal/VENDORING_TFIDF_ACRONYM_CLEANUP_PATCH_MANIFEST.md`

## Retired touched legacy files

- `src/Service/Search/VendorTfidfSearchService.php`
- `src/ServiceInterface/Search/VendorTfidfSearchServiceInterface.php`

## Compatibility note

The old files are retired by the apply script with `.bak` copies. The new service and interface keep the same methods and behavior.
