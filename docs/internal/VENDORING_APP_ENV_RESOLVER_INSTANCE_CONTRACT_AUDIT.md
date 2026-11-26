# Vendoring app env resolver instance contract audit

Wave AA removes the remaining static environment resolver usage from active runtime/surface-builder code.

## Findings

- `VendorAppEnvResolverInterface` exposed a static `resolve()` method, which made DI aliases less useful and encouraged static calls through the interface.
- `VendorLocalDevSurfaceBuilder` called `VendorAppEnvResolverInterface::resolve()` directly instead of using an injected service.
- Runtime observability services imported the concrete `VendorAppEnvResolver` only to call the static resolver.

## Changes

- Converted `VendorAppEnvResolverInterface::resolve()` to an instance method.
- Converted `VendorAppEnvResolver::resolve()` to an instance method.
- Injected `VendorAppEnvResolverInterface` into `VendorLocalDevSurfaceBuilder`.
- Injected `VendorAppEnvResolverInterface` into runtime logger and metric collector services.
- Removed concrete service imports from observability services.

## Scope boundary

No service relocation, namespace change, or runtime-proof phase was introduced in this wave. The existing DI alias remains the canonical binding.
