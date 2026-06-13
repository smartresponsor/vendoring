# Vendoring policy smoke root cleanup audit

## Scope

This wave removes the remaining root-level `scripts/` bucket from the active application root and classifies the policy smoke scripts under `ops/policy/smoke/`.

## Findings

- The root `scripts/` directory only contained policy smoke shell scripts.
- Those scripts are operational policy checks, not source code, runtime configuration, or deploy descriptors.
- Keeping them at root weakened the root cleanup milestone because the root kept an untyped miscellaneous script bucket.

## Changes

- Moved `scripts/*_smoke.sh` to `ops/policy/smoke/*_smoke.sh`.
- Removed the empty root `scripts/` directory from the cumulative snapshot.
- No PHP namespaces, service aliases, entities, migrations, or Docker descriptors were changed.

## Safety

- This is a non-destructive overlay patch with explicit legacy-path cleanup in the apply script.
- The patch does not delete the repository, overwrite the full project root, or remove unrelated files.
