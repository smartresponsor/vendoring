# Vendoring Wave 52 - repository/controller contracts

## Delivered
- Declared direct runtime requirement on `doctrine/persistence` because repositories import `Doctrine\Persistence\ManagerRegistry` explicitly.
- Added repository contract test slice for Doctrine-backed repositories and their entity bindings.
- Added controller infrastructure contract test slice for route-backed JSON controllers.
- Extended smoke checks to require `test:repository` and `doctrine/persistence`.

## Why this wave matters
Wave 51 closed the broad Doctrine package surface, but the current slice still relied directly on `Doctrine\Persistence\ManagerRegistry` without declaring the package. This wave closes that dependency gap and anchors two infrastructure-facing seams:
- Doctrine repository base/constructor/entity binding contracts
- Symfony controller base/route/JsonResponse contracts

## Verified locally in container
- PHP lint on changed files: green
- `php tests/bin/smoke.php`: green
