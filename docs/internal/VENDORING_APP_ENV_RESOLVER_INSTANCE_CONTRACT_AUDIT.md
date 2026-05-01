# Vendoring app env resolver instance contract audit

Wave AA removes the remaining static environment resolver usage from active runtime/controller code.

## Findings

- `VendorAppEnvResolverServiceInterface` exposed a static `resolve()` method, which made DI aliases less useful and encouraged static calls through the interface.
- `VendorLocalDevController` called `VendorAppEnvResolverServiceInterface::resolve()` directly instead of using an injected service.
- Runtime observability services imported the concrete `VendorAppEnvResolverService` only to call the static resolver.

## Changes

- Converted `VendorAppEnvResolverServiceInterface::resolve()` to an instance method.
- Converted `VendorAppEnvResolverService::resolve()` to an instance method.
- Injected `VendorAppEnvResolverServiceInterface` into `VendorLocalDevController`.
- Injected `VendorAppEnvResolverServiceInterface` into runtime logger and metric collector services.
- Removed concrete service imports from observability services.

## Scope boundary

No service relocation, namespace change, or runtime-proof phase was introduced in this wave. The existing DI alias remains the canonical binding.
