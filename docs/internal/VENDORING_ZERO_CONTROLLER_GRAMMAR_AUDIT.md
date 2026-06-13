# Vendoring zero-controller grammar audit

Current patch goal: move Vendoring away from component-owned Symfony controller routes and toward Cruding-owned URI grammar with canonical `App\Vendoring\Service\Http\*` runtime services.

## Applied decisions

- `config/vendor_routes.yaml` is retired.
- `config/component/routes.yaml` no longer imports controller routes.
- `src/Kernel.php` no longer imports `config/vendor_routes.yaml` in standalone mode.
- `config/platform/routes/crud/vendor.yaml` stores the vendor CRUD route-map registry.
- `config/platform/routes/business/vendor.yaml` stores the vendor business route-map registry.
- `App\Vendoring\Service\Http\` is registered as a canonical service namespace for Cruding FQCN lookup.
- The current observed surface `/vendor/attachment/document/index` is backed by `App\Vendoring\Service\Http\Vendor\Attachment\Document\VendorAttachmentDocumentIndexService`.

## Remaining legacy entrypoints to evacuate

The old `src/Controller/Vendor/*SurfaceBuilder.php` files still exist in this patch as source material. They are no longer exported as Symfony services or routes. Each should be physically split into canonical `App\Vendoring\Service\Http\Vendor\...\*Service` classes according to Cruding diagnostics and route-map keys.

Do not re-enable `config/vendor_routes.yaml`.
