# Vendoring core entrypoint responsibility

The Vendor core route-map is no longer treated as a checklist of generic CRUD operations.

Active surface:

- `vendor.index` → `VendorIndexService` → `vendor/index.html.twig`
- `vendor.show_id` → `VendorShowService` → `vendor/show.html.twig`
- `vendor.new` → `VendorNewService` → `vendor/form.html.twig`
- `vendor.create` → `VendorCreateType` + `VendorCreateService`
- `vendor.edit_id` → `VendorEditService` → `vendor/form.html.twig`
- `vendor.update_id` → `VendorUpdateType` + `VendorUpdateService`

Why slug routes are not active:

`VendorEntity` has an integer identifier and no slug field or slug repository lookup.

Why delete/bulk/import/export/archive/restore/duplicate are not active:

The current core business service proves only `create()` and `update()`. A route is not registered until a corresponding business capability exists outside the HTTP entrypoint.

The HTTP classes may remain physically present for later classification, but they are not active route targets.

Viewing ownership:

Vendoring emits template candidates. Viewing resolves/render them, optionally through Interfacing. Vendoring does not depend directly on Interfacing.
