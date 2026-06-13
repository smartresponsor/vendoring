# Canon tools

This folder contains lightweight repo scanners that enforce structural invariants.

## vendor-canon-scan

Runs path + namespace heuristics to detect:
- nested `src/src` trees
- repeated layer segments (`Entity/Entity`, `Controller/Controller`, ...)
- archive directories inside `src/` (e.g. `vendor-current`, `vendor-sketch-*`, `123_vendor-*`)
- namespace roots other than `App\Vendoring\\*` and forbidden non-App namespace chains in runtime/config/test surfaces

Run:

```bash
node tools/canon/vendor-canon-scan.mjs
```

Reports:
- `build/reports/canon/vendor-canon-scan.txt`
- `build/reports/canon/vendor-canon-scan.json`

## migration-dialect-guard

Validates that SQL migrations are placed under dialect-specific folders.

Run:

```bash
node tools/canon/migration-dialect-guard.mjs
```

Reports:
- `build/reports/canon/migration-dialect-guard.txt`
- `build/reports/canon/migration-dialect-guard.json`
