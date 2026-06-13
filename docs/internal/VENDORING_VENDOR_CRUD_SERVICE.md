# VendorCrudService

`VendorCoreService` was renamed to `VendorCrudService` because its proven responsibility is the Vendor CRUD application workflow, not the whole Vendor component core.

Supported business operations:

- `index()`
- `find(id)`
- `create(VendorCreateDTO)`
- `update(VendorEntity, VendorUpdateDTO)`

The active `Vendor*Service` HTTP entrypoints delegate to `VendorCrudServiceInterface`.

Not supported:

- delete
- archive/restore
- duplicate
- import/export
- bulk mutation
- slug resolution

Those operations remain outside the active route surface until their business services exist.
