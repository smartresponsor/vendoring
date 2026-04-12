# Vendoring

Vendoring is a Symfony 8 component that owns the **vendor lifecycle and operational readiness** surface.
It is focused on vendor onboarding/profile/readiness contracts and does **not** replace Ordering, Billing, Paying, Shipping, or Taxation domains.

## Product positioning (2–3 minute read)

### What Vendoring is

Vendoring provides:

- vendor identity and profile management
- vendor capability/status and readiness state
- vendor settlement prerequisites and payout readiness
- vendor-facing operational metadata (ownership, finance/runtime profile, external integration state)

### What Vendoring is not

Vendoring is not:

- **Ordering** (cart/order capture/orchestration)
- **Billing** (invoice generation, billing cycles, customer invoicing)
- **Paying** (payment gateway authorization/capture for buyers)
- **Shipping** (fulfillment/logistics/carrier orchestration)
- **Taxation** (tax calculation, filing, remittance)

Vendoring only stores and exposes vendor-side prerequisites/state consumed by those domains.

### Responsibility boundary

| Domain | Owns in Vendoring | Out of scope in Vendoring |
|---|---|---|
| Vendor onboarding | vendor creation/profile completion/readiness checks | procurement/legal negotiations workflow |
| Vendor identity/profile | canonical vendor profile and ownership projections | customer profile, buyer KYC |
| Vendor capability/status | readiness/runtime capability signals | order-level fulfillment orchestration |
| Settlement prerequisites | payout account + readiness gating metadata | PSP charge flow, payout rail execution engine internals |
| Contract/eligibility metadata | eligibility flags and runtime-operational state | legal document drafting and signing platform |

## Minimal use case

The smallest useful integration is:

1. Create/update vendor profile.
2. Read runtime/ownership status for gating.
3. Create and process payout once thresholds are met.

This gives a production-relevant “vendor readiness + payout prep” path without adopting every sub-surface.

## Real usage scenarios

1. **Vendor onboarding**: back-office app creates vendor profile, checks ownership/runtime readiness, then marks vendor operationally ready.
2. **Vendor identity/profile maintenance**: partner portal updates legal/trading profile and reads normalized projection.
3. **Capability/status checks before activation**: orchestrator reads runtime status and external integration state before enabling downstream flows.
4. **Settlement prerequisites**: finance service verifies payout account and readiness status before requesting payout.
5. **Contract/eligibility metadata**: compliance or operations tools read vendor ownership/finance projections to enforce eligibility rules.

## Consumer quick start

### 1) Install

```bash
composer require symfony/framework-bundle symfony/validator symfony/form doctrine/orm
composer install
```

### 2) Route + service wiring

Import vendoring routes/services in your Symfony app:

```yaml
# config/routes/vendor.yaml
vendoring:
  resource: '../../vendor/.../config/vendor_routes.yaml'
```

```yaml
# config/services.yaml
imports:
  - { resource: '../../vendor/.../config/vendor_services.yaml' }
```

> In this repository itself, these files already exist under `config/vendor_routes.yaml` and `config/vendor_services.yaml`.

### 3) Runtime configuration

```env
VENDOR_DSN=pgsql://app:app@127.0.0.1:5432/vendoring
VENDOR_SQLITE_DSN=sqlite:///%kernel.project_dir%/var/vendor_runtime.sqlite
APP_ENV=dev
APP_SECRET=change-me
```

- PostgreSQL: user/business data
- SQLite: application/runtime local data and deterministic runtime slices

### 4) Enable only required parts

If you only need transaction + payout surfaces, keep only required routes in consumer routing config:

```yaml
# config/routes/vendoring_minimal.yaml
vendor_transactions:
  path: /api/vendor-transactions
  controller: App\Controller\VendorTransactionController

payout:
  path: /api/payout
  controller: App\Controller\Payout\PayoutController
```

In DI, you can alias only required interfaces in slim deployments and skip optional runtime/ops consumers.

## API surface (consumer-oriented)

### Endpoint groups

- **Vendor profile and ownership**
  - `GET /api/vendor-profile/vendor/{vendorId}`
  - `PATCH /api/vendor-profile/vendor/{vendorId}`
  - `GET /api/vendor-ownership/vendor/{vendorId}`
- **Vendor runtime views**
  - `GET /api/vendor/runtime/{vendorId}/finance`
  - `GET /api/vendor/runtime/{vendorId}/external-integrations`
  - `GET /api/vendor/runtime/{vendorId}/statement-delivery`
- **Transactions and payout prep**
  - `POST /api/vendor-transactions`
  - `GET /api/vendor-transactions/vendor/{vendorId}`
  - `POST /api/vendor-transactions/vendor/{vendorId}/{id}/status`
  - `POST /api/payout/create`
  - `POST /api/payout/process/{payoutId}`
  - `GET /api/payout/{payoutId}`

### Request/response flow example

**Create vendor transaction**

```http
POST /api/vendor-transactions
Authorization: Bearer <token>
Content-Type: application/json

{
  "vendorId": "42",
  "orderId": "ORD-2026-0001",
  "projectId": "PRJ-ALPHA",
  "amount": "120.50"
}
```

```json
{
  "id": 1001,
  "status": "new"
}
```

**Error model (stable shape)**

```json
{
  "error": "transaction_validation_error",
  "hint": "Actionable next step for the consumer."
}
```

Common error codes:

- `malformed_json` (400)
- `authentication_required` (401)
- `invalid_api_token` (401)
- `permission_denied` (403)
- `not_found` or `vendor_not_found` (404)
- `duplicate_transaction` (409)
- validation/invariant violations (422)
- `tenant_id_required`, `statement_from_required`, `statement_to_required` (422 for statement/runtime query validation)
- `retention_fee_percent_required`, `retention_fee_percent_out_of_range`, and `payout_validation_error` (422 for payout create validation)

Authentication expectation: Bearer token in `Authorization` header for write operations.

## Demo narrative (product story)

1. **Create vendor** (profile upsert).
2. **Review vendor state** (ownership/runtime projections).
3. **Activate/qualify vendor** (status and prerequisite checks).
4. **Read operational profile** (finance/external integration runtime views).
5. **Prepare commercial readiness** (transaction + payout readiness and processing).

See also: `docs/integration.md`.

## Canonical vocabulary

Use these terms consistently in code/docs/API:

- **Vendor**: canonical term for the managed party in this component.
- **Supplier / Merchant / Partner / Seller**: allowed only when mapping external upstream terms; normalize back to `vendor` in API and internal contracts.

## Runtime confidence lanes

```bash
composer quality:runtime
composer quality:contracts
composer quality:api
composer quality
```

## Release-candidate documentation

- `docs/modules/ROOT/pages/positioning.adoc`
- `docs/modules/ROOT/pages/boundaries.adoc`
- `docs/modules/ROOT/pages/use-cases.adoc`
- `docs/release/RC_OPENAPI_SURFACE.md`
- `docs/release/RC_RUNTIME_SURFACES.md`
- `docs/release/RC_GATE_CHECKLIST.md`

Generated artifacts:

- `build/docs/openapi.json`
- `build/docs/openapi.yaml`
- `build/docs/phpdocumentor/`
- `build/release/`
