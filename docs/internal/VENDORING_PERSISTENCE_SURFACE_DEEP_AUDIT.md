# Vendoring Wave 11 — Persistence Surface Deep Audit

## Scope

This wave starts from the Wave 10 cumulative Vendoring slice and does not assume that any absent
`Entity`, `Repository`, `Projection`, `Event`, `EventInterface`, `Policy`, or `PolicyInterface` files exist.

The goal is not to rebuild persistence. The goal is to stop registering persistence-bound code as live Symfony services
while preserving the files for a later intentional redesign.

## Findings

### 1. Persistence surface is absent

The current component has no active implementation directories for the namespaces that many services import:

- `App\Vendoring\Entity\Vendor\*`
- `App\Vendoring\Repository\Vendor\*`
- `App\Vendoring\RepositoryInterface\Vendor\*`
- `App\Vendoring\Projection\Vendor\*`
- `App\Vendoring\Event\Vendor\*`
- `App\Vendoring\EventInterface\Vendor\*`
- `App\Vendoring\Policy\Vendor\*`
- `App\Vendoring\PolicyInterface\Vendor\*`

### 2. Service graph is mixed

There are two distinct groups:

#### Still live / container-safe

These areas do not directly depend on the absent persistence surface and can remain registered:

- API request resolvers
- command JSON/result helpers
- category collection/rule/search helpers after namespace correction
- observability
- rollout
- runtime env resolver
- traffic limiter
- outbound policy helpers
- security HMAC / authorization matrix
- statement request/export/mailer support
- webhooks consumer
- selected ops services that read file-backed release artifacts

#### Quarantined / persistence-bound

These areas import missing persistence or projection contracts and must not be autowired until the persistence layer is restored or rewritten:

- assignment
- billing
- catalog merch/review assignment
- core vendor write model
- documents
- finance runtime projection
- identity/passport
- integration projections
- ledger
- media read/write bridge
- metrics backed by ledger
- ownership projection builder
- payout write/account/settlement services
- profile projections/profile write-side/legacy attachment resolver
- security access/API-key/persistence-backed state projection
- statement recipient/ledger statement/delivery projection
- syndication services using missing events/policies
- transaction lifecycle
- API-key and payout commands that require missing repositories
- all fixtures using missing entities/repositories

### 3. Category namespace drift was real

Files were located under:

- `src/Service/Category/*`
- `src/ServiceInterface/Category/*`

but declared namespaces under `CategoryEntity`.

Wave 11 corrects them to:

- `App\Vendoring\Service\Category\*`
- `App\Vendoring\ServiceInterface\Category\*`

This makes the existing service aliases match the actual PHP declarations.

## Changes in Wave 11

### Config quarantine

`config/component/services.yaml` no longer imports the transaction repository wiring file because the repository layer is absent.

The transaction service file is kept as a compatibility stub:

- `config/vendor_services_transactions.yaml`

### Autowire quarantine

The main `App\Vendoring\` resource now excludes persistence-bound services and unsafe commands from container discovery.
The source files are intentionally kept in place for later review.

### Alias quarantine

Unsafe service aliases were removed when either:

- the alias interface does not exist,
- the target service does not exist,
- the alias interface imports missing persistence/projection/event types,
- the target service imports missing persistence/projection/event types.

The removed alias list is stored at:

- `delivery/audit/vendoring-wave11-quarantined-service-aliases.json`

## Important non-change

Wave 11 does not delete the persistence-bound PHP classes.

Reason: the current slice does not contain replacement entities/repositories/projections. Physical deletion at this point would destroy information useful for rebuilding the canonical Vendoring persistence model.

## Next recommended wave

Wave 12 should be one of these, not both at once:

1. **Vendoring route/http surface repair**
   - `config/platform/routes/*` points to `App\Vendoring\Service\Http\Vendor\*`, but `src/Service/Http` is absent.
   - Decide whether routes stay registry-only or generate Symfony-oriented thin HTTP services.

2. **Vendoring persistence rebuild design**
   - decide the actual Doctrine record set,
   - create only canonical `*Record` entities where durable storage is needed,
   - keep runtime DTO/projection classes separate from Doctrine entities.
