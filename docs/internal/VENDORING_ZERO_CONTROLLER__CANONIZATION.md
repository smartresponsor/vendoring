# Vendoring Wave 12D — Zero-Controller Surface Canonization

Vendoring is normalized as a zero-controller component. Route-map targets remain Symfony-service based and must resolve to `Vendor*Service` entrypoints. Form targets must resolve to `Vendor*Type` classes.

## Canon

- No `Controller/` surface is introduced.
- Route service targets are `App\Vendoring\Service\Http\Vendor\...\Vendor*Service`.
- Form targets are `App\Vendoring\Form\Vendor\...\Vendor*Type`.
- Read routes remain executable through the read route response service.
- Write/business mutation routes remain inert while the Vendor persistence/domain surface is quarantined.
- Vendoring does not depend directly on Interfacing contracts.
- Cruding owns route/operation grammar; Viewing-compatible response contracts are returned by Vendor HTTP services.

## Main change

`VendorReadRouteResponseFactory` was replaced by `VendorHttpRouteResponseService` so the shared helper also follows the Vendor `*Service` naming canon.

Blocked skeleton services now return a deliberate contract:

```text
status: route_blocked
persistence: quarantined
mutationAllowed: false
```

This is safer than a generic `skeleton` status because the route target is resolvable but explicitly non-mutating.

## Verification

Run:

```bash
php tools/qa/VendoringZeroControllerSurfaceAudit.php
```

Expected:

```text
Vendoring zero-controller surface audit OK
Controller files: 0
```
