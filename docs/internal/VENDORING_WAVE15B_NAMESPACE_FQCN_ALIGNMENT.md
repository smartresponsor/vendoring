# Vendoring Wave 15B — Namespace / FQCN Alignment

## Scope

The component Composer namespace is `App\Vendoring\ => src/`.

Wave 15B aligns the complete HTTP and Form surfaces:

- `App\Service\Http\...` → `App\Vendoring\Service\Http\...`
- `App\Form\...` → `App\Vendoring\Form\...`

The update includes PHP namespace declarations, imports, route-map targets, tests,
smoke tools, QA tools, configuration, documentation and audit artifacts.

## Result

- 78 HTTP service classes are component-namespaced.
- 46 Form classes are component-namespaced.
- All `src/` PHP namespaces match `App\Vendoring\` PSR-4 paths.
- All route `service:` and `type:` FQCNs point to physical files.
- No controller is introduced.
- No entity, repository or business relation is changed.
- No file deletion is required.

## Verification

```bash
php tools/qa/VendoringNamespaceFqcnAlignmentAudit.php
composer dump-autoload
```
