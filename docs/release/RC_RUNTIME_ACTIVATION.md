# RC Runtime Activation

This wave prepares live runtime activation for the release-candidate surface without breaking environments that still run on the lean dependency set.

## Activated when packages are installed

The runtime activation layer is guarded by `class_exists()` checks and becomes active when the following packages are available:

- `twig/twig`
- `symfony/twig-bundle`
- `symfony/form`
- `symfony/validator`
- `symfony/security-csrf`
- `nelmio/api-doc-bundle`

## Runtime seams

- `config/packages_runtime.php` conditionally enables Twig, Forms, Validation, CSRF and Nelmio.
- `config/routes_runtime.php` conditionally mounts `/api/doc`.
- `config/services_runtime.php` conditionally registers form types.
- `templates/ops/vendor_transactions/index.html.twig` provides the server-rendered operator surface.
- `src/Form/Ops/*` provides Symfony form DTO and type definitions.

## Current compatibility strategy

Until the additional packages are installed, the operator surface continues to use the dependency-light HTML builder. Once the packages are installed, the controller can switch to the Twig/Form rendering branch without changing routes or public entry points.

## Lock/install follow-up

Because this delivery was prepared in an offline environment, `composer.lock` was not regenerated here. After applying the cumulative snapshot, refresh the lock file with a normal dependency install/update flow so the Twig/Form/Nelmio packages become available to CI and local runtime.


## CI activation note

The runtime and aggregate RC workflows should install PHP with `pdo_sqlite` enabled so the kernel-handled vertical slice and fresh-database boot proofs run as real runtime checks rather than environment skips.
