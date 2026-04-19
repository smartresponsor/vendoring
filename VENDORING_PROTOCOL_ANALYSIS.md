# Vendoring current-slice protocol audit

Base: current slice only, from the uploaded archive `Vendoring.zip`.
Scope: protocol/canon audit against the user-declared Vendoring/Vendor rules.

## Executive verdict

The slice is **not canon-compliant** in its current state.

The active code under canonical roots such as `src/Controller/Vendor`, `src/DTO/Vendor`, `src/Entity/Vendor`, `src/Event/Vendor`, `src/Repository/Vendor`, `src/Service/Vendor`, and `src/ValueObject/Vendor` is present and aligned with the intended `App\Vendoring\ -> src/` production root.

However, the repository also contains a large amount of **structural debris** and **forbidden competing trees**, mostly as empty directories. The main debt is structural, not business-code correctness.

## Canonical positives

- `composer.json` uses `App\Vendoring\\` => `src/` for PSR-4 autoload.
- Core Vendor responsibility is placed under canonical layer roots, for example:
  - `src/Controller/Vendor/...`
  - `src/DTO/Vendor/...`
  - `src/Entity/Vendor/...`
  - `src/Event/Vendor/...`
  - `src/Repository/Vendor/...`
  - `src/Service/Vendor/...`
  - `src/ValueObject/Vendor/...`
- Tests are rooted under `tests/`, not under forbidden `test/Vendor/...` or `test/Vendoring/...` trees.
- `src/Infrastructure/...` exists in the canonical spelling.

## Major violations found in the uploaded slice

### 1. Forbidden competing production roots outside `src/`

These top-level directories existed outside the canonical production root:

- `Event/`
- `Repository/`
- `Service/`
- `ValueObject/`
- `vendoring_repo_winner/`

In this slice they were empty, but they still compete with the single-root rule.

### 2. Forbidden roots under `src/`

The slice contained forbidden roots under `src/`:

- `src/Adapter`
- `src/Domain`
- `src/DomainInterface`
- `src/src`

This conflicts with the declared Symfony-oriented single-root canon and the explicit ban on Port/Adapter/Hexagonal skeletons.

### 3. Forbidden Port/Adapter traces

The slice contained forbidden Port/Adapter naming:

- `src/Adapter`
- `src/ServiceInterface/Vendor/Adapter`
- `src/ServiceInterface/Vendor/Port`

Even though these were empty in this slice, the structure itself is non-canonical.

### 4. Repository/domain-name directories too shallow under `src/`

The protocol explicitly forbids these patterns:

- `src/Vendor/...`
- `src/VendorInterface/...`
- `src/Vendoring/...`
- `src/VendoringInterface/...`
- `src/.../Vendor/...` (too shallow)
- `src/.../Vendoring/...` (too shallow)

This slice contains many shallow `Vendor` directories at depth 2-3, for example:

- `src/Command/Vendor`
- `src/Controller/Vendor`
- `src/DTO/Vendor`
- `src/Entity/Vendor`
- `src/Event/Vendor`
- `src/Repository/Vendor`
- `src/Service/Vendor`
- `src/ValueObject/Vendor`

Under the user’s declared Vendoring/Vendor protocol, these are violations even if they contain the main implementation.

### 5. Structural archaeology / fossil trees

Many empty directories indicate prior wave residue rather than living code, including:

- `vendor-current`
- `vendor-sketch-*`
- `vendor-bin`
- repeated nested trees like `.../Controller/Controller/...`, `.../Entity/Entity/...`, `.../Service/Service/...`

These materially reduce predictability of the repository shape.

## What was safe to clean in this wave

Only **empty non-canonical directories** were removed.
No non-empty PHP source file was rewritten.
No namespace or runtime logic was changed.

Safe cleanup included:

- empty top-level competing roots outside `src/`
- empty forbidden roots like `src/Adapter`, `src/Domain`, `src/DomainInterface`, `src/src`
- empty `vendor-current`, `vendor-sketch-*`, `vendor-bin`
- empty residual nested structural trees that became removable after the above cleanup

## Important architectural note

There is a tension inside the supplied protocol:

- Rule set A says separate interface roots such as `src/[Layer]Interface/*` are canonical.
- Rule set B simultaneously forbids shallow `src/.../Vendor/...` and `src/.../Vendoring/...` shapes.

The current implementation uses canonical layer roots plus shallow `Vendor` responsibility placement, e.g. `src/Service/Vendor/...` and `src/Repository/Vendor/...`.

So, under the supplied protocol, the slice can be cleaned structurally, but **full compliance would require a deeper responsibility placement redesign**, not just debris pruning.

## Recommended next wave

1. Confirm the exact allowed minimum depth for `Vendor` responsibility placement under `src/`.
2. If the rule is strict, redesign living code from shallow patterns like `src/Service/Vendor/...` toward a deeper canonical placement.
3. Keep the two-archive delivery format:
   - touched-files flat zip
   - cumulative snapshot flat zip

## Delivered artifacts

This wave provides:

- a touched-files archive with the protocol report
- a cumulative snapshot archive with only safe structural cleanup applied

