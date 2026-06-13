# Vendoring Gate Form Canon Sync Audit

Wave S synchronizes the gate contract documentation with the active Form-layer canon introduced by the previous form cleanup waves.

## Finding

- `.gate/contract/README.md` still described `src/Form/**` as requiring `Vendor*Type.php` / `Vendor*Type`.
- Active source files already use `Vendor*Form.php` / `Vendor*Form`.
- The stale gate text could cause future agents or CI policy prompts to recreate `*Type` classes after the Form-layer cleanup.

## Change

- Updated the gate contract to require `Vendor*Form.php` / `Vendor*Form`.
- Kept the Symfony implementation detail explicit: classes may still extend Symfony `AbstractType`; only the ecosystem file/class suffix is `Form`.

## Scope

- No PHP runtime code changed.
- No deletions required.
- No namespace change.
