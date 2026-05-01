# Vendoring root contract file sync patch manifest

## Touched files

- `.gate/contract/contract.json`
- `.gate/contract/README.md`
- `docs/internal/VENDORING_ROOT_CONTRACT_FILE_SYNC_AUDIT.md`
- `docs/internal/VENDORING_ROOT_CONTRACT_FILE_SYNC_PATCH_MANIFEST.md`

## Deletes

None.

## Validation targets

```bash
composer dump-autoload
php bin/console lint:container
php tests/bin/interface-alias-smoke.php
```
