# RC Release Manifest

This document describes the release manifest surface used to decide whether a release candidate may proceed.

## Purpose

The release manifest combines:
- release documentation presence
- generated build artifacts under `build/release/`
- monitoring snapshot status
- evaluated alert codes
- synthetic probe availability

## Runtime surface

### HTTP

```bash
GET /api/vendor-monitoring/release-manifest?windowSeconds=900
```

### CLI

```bash
php bin/console app:vendor:release-manifest --windowSeconds=900 --format=json --write
```

## Output

The manifest contains:
- `releaseDocs`
- `buildArtifacts`
- `monitoring`
- `status`

## Status semantics

- `ok` means release artifacts and monitoring summary are green enough to continue validation
- `warn` means at least one release artifact, probe, or monitoring signal requires attention before promotion
