# Canon tools

This folder contains lightweight repo scanners that enforce structural invariants.

## vendor-canon-scan

Runs path + namespace heuristics to detect:
- nested `src/src` trees
- repeated layer segments (`Entity/Entity`, `Controller/Controller`, ...)
- archive directories inside `src/` (e.g. `vendor-current`, `vendor-sketch-*`, `123_vendor-*`)
- mixed namespace families (e.g. `SmartResponsor\\*` inside an `App\\*` runtime repo)

Run:

```bash
node tools/canon/vendor-canon-scan.mjs
```

Reports:
- `.report/vendor-canon-scan.txt`
- `.report/vendor-canon-scan.json`

## migration-dialect-guard

Validates that SQL migrations are placed under dialect-specific folders.

Run:

```bash
node tools/canon/migration-dialect-guard.mjs
```

Reports:
- `.report/migration-dialect-guard.txt`
- `.report/migration-dialect-guard.json`
