# Vendoring RC Baseline

This document describes the calm release-facing baseline snapshot added after the
runtime status contour became available.

## Command

```bash
php bin/console app:vendor:release-baseline --tenantId=tenant-1 --vendorId=42 --format=json
```

Optional flags:
- `--from=YYYY-MM-DD`
- `--to=YYYY-MM-DD`
- `--currency=USD`
- `--write`
- `--output=build/release/custom-vendor-baseline.json`

## Purpose

The baseline snapshot is **not** a new business workflow. It is a release-facing
summary built on top of the vendor-local runtime surfaces:
- ownership
- finance
- statement delivery
- external integration

It also checks the presence of internal canon artifacts that document the vendor
identity/access foundation.
