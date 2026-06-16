# Vendoring Wave 12A — HTTP Route Surface Repair Audit

## Scope

Wave 12A repairs the route-map target surface after Wave 11 quarantined the broken persistence-bound services.

The `config/platform/routes/**` files are registry/protocol files consumed by Cruding. They referenced HTTP service targets under `App\Vendoring\Service\Http\Vendor\*` and form targets under `App\Vendoring\Form\Vendor\*`, but the corresponding files were absent from `src/`.

## Result

Added resolvable skeleton targets:

- HTTP service skeletons: 54 unique route-map service targets.
- Form type skeletons: 42 unique route-map form targets.

The skeletons intentionally do not restore persistence behavior. They only make the route-map FQCN surface coherent while Vendoring persistence remains quarantined.

## Canon

- No controllers were added.
- No Doctrine entities were added.
- No repository layer was restored.
- The route-map remains registry-only.
- HTTP surface classes live under `src/Service/Http` with the default Symfony `App\Service\Http` namespace used by the route-map.
- Form surface classes live under `src/Form` with the default Symfony `App\Form` namespace used by the route-map.

## Next Step

Wave 12B should decide which skeleton route targets become real HTTP application services and which route-map entries should remain protocol-only or be removed.
