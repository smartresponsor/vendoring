# Vendoring Commanding Runtime Log Root Cleanup Patch Manifest

## Touched files

- `.commanding/lib/ui.sh`
- `.commanding/sh/health-lib.sh`
- `docs/internal/VENDORING_COMMANDING_RUNTIME_LOG_ROOT_CLEANUP_AUDIT.md`
- `docs/internal/VENDORING_COMMANDING_RUNTIME_LOG_ROOT_CLEANUP_PATCH_MANIFEST.md`

## Touched deletions

- `logs/actions.log`
- `logs/` when empty

## Validation

Recommended after applying the patch:

```bash
composer dump-autoload
php bin/console lint:container
php tests/bin/interface-alias-smoke.php
```
