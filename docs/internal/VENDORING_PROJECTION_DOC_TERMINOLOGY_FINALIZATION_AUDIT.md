# Vendoring Projection Documentation Terminology Finalization Audit

## Scope

Wave J completes the active documentation and PHPDoc terminology follow-up after the projection class and builder renames.

## Findings

The active source comments and user-facing documentation no longer contained `Vendor*View` classes, but several descriptions still called projection payloads `view` objects. That wording was safe at runtime but weak as a future guardrail because it could encourage agents or contributors to reintroduce `View`-named symbols in `src/Projection`.

Historical wave audit/manifest files were intentionally left unchanged where they describe old-to-new rename history.

## Changes

- Updated active projection PHPDoc from `view` to `projection` wording.
- Updated the runtime status projection builder PHPDoc.
- Updated install documentation from `runtime view` to `runtime projection`.
- Updated behavioral scenario and PHPDoc guide wording so projection terminology is consistent with `Vendor*Projection.php` and `*ProjectionBuilderService` canon.

## Deletions

None.

## Validation notes

This is a documentation/comment-only cleanup plus active source PHPDoc wording. It does not alter constructor signatures, service aliases, route names, or runtime logic.
