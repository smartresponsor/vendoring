# Vendoring root contract file sync audit

## Scope

Wave AD synchronizes the root contract with the cleaned Vendoring repository root.

## Findings

- `.gate/contract/contract.json` still required a root-level `MANIFEST.json`.
- The actual cleaned repository root keeps `composer.json` as the Symfony/Composer component manifest.
- Gate-owned manifests live under `.gate/`, so requiring a root `MANIFEST.json` would recreate root noise instead of enforcing the current structure.

## Change

- Required root files are now `.gitignore`, `README.md`, and `composer.json`.
- Allowed exact root files match that same set.
- Allowed non-dot root directories are explicitly listed so normal Symfony/application directories are not treated as accidental drift.

## Non-goals

- No source namespace changes.
- No service/interface renames.
- No deletion of application directories.
