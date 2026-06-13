# Vendoring Layer 3 Structure and Naming Canon

This file is machine-readable operational canon for agents and code generators. Do not paraphrase it when editing code.

## Current runtime stance

Vendoring is a zero-controller / zero-route component.

Runtime dispatch is owned by Cruding:

```text
URI
→ Cruding grammar
→ providerKey / routeKey
→ App\Vendoring\Service\Http\Vendor\...\*Service
→ optional App\Vendoring\Form\Vendor\...\*Type
```

Forbidden active runtime layers:

- `src/Controller/`
- `src/ControllerTrait/`
- the retired component route file
- Symfony `#[Route]` attributes in component source

## Locked literal rule

For the Vendoring component, these Layer 3 source folders are locked to a single Vendor bucket when present:

- `src/EntityInterface/Vendor/`
- `src/Event/Vendor/`
- `src/EventInterface/Vendor/`
- `src/Policy/Vendor/`
- `src/PolicyInterface/Vendor/`
- `src/Repository/Vendor/`
- `src/RepositoryInterface/Vendor/`
- `src/Projection/Vendor/`

The parent folders listed above must not contain PHP files directly and must not contain any bucket other than `Vendor`.

## Folder-by-folder naming contract

| Layer folder | Only allowed direct child folder | Allowed PHP files inside child folder | Required namespace |
|---|---|---|---|
| `src/EntityInterface/` | `Vendor` | `Vendor*EntityInterface.php` | `App\Vendoring\EntityInterface\Vendor` |
| `src/Event/` | `Vendor` | `Vendor*Event.php` | `App\Vendoring\Event\Vendor` |
| `src/EventInterface/` | `Vendor` | `Vendor*EventInterface.php` | `App\Vendoring\EventInterface\Vendor` |
| `src/Policy/` | `Vendor` | `Vendor*Policy.php` | `App\Vendoring\Policy\Vendor` |
| `src/PolicyInterface/` | `Vendor` | `Vendor*PolicyInterface.php` | `App\Vendoring\PolicyInterface\Vendor` |
| `src/Repository/` | `Vendor` | `Vendor*Repository.php` | `App\Vendoring\Repository\Vendor` |
| `src/RepositoryInterface/` | `Vendor` | `Vendor*RepositoryInterface.php` | `App\Vendoring\RepositoryInterface\Vendor` |
| `src/Projection/` | `Vendor` | `Vendor*Projection.php` | `App\Vendoring\Projection\Vendor` |

## HTTP service and form exception

`src/Service/Http/` and `src/Form/` follow Cruding FQCN convention, not the `App\Vendoring\...` namespace.

Allowed:

- `src/Service/Http/Vendor/VendorIndexService.php`
- `src/Service/Http/Vendor/Attachment/Document/VendorAttachmentDocumentIndexService.php`
- namespace `App\Vendoring\Service\Http\Vendor\...`
- `src/Form/Vendor/VendorCreateType.php`
- namespace `App\Vendoring\Form\Vendor\...`

Forbidden:

- `src/Controller/`
- `src/ControllerTrait/`
- retired controller namespace classes
- `App\Vendoring\Service\Http\...`

## Forbidden examples

These paths are forbidden even when the class name looks meaningful:

- `src/Event/Payout/PayoutCreatedEvent.php`
- `src/Event/CategorySyndicationPublishPackageBuilt.php`
- `src/EventInterface/PayloadEventInterface.php`
- `src/Policy/CategorySyndicationMappingPolicy.php`
- `src/PolicyInterface/CategorySyndicationMappingPolicyInterface.php`
- `src/Repository/Ledger/LedgerEntryRepository.php`
- `src/Repository/Payout/PayoutRepository.php`
- `src/RepositoryInterface/Ledger/LedgerEntryRepositoryInterface.php`
- `src/RepositoryInterface/Payout/PayoutRepositoryInterface.php`

Canonical replacements must be literal, for example:

- `src/Event/Vendor/VendorPayoutCreatedEvent.php`
- `src/Event/Vendor/VendorCategorySyndicationPublishPackageBuiltEvent.php`
- `src/EventInterface/Vendor/VendorPayloadEventInterface.php`
- `src/Policy/Vendor/VendorCategorySyndicationMappingPolicy.php`
- `src/PolicyInterface/Vendor/VendorCategorySyndicationMappingPolicyInterface.php`
- `src/Repository/Vendor/VendorLedgerEntryRepository.php`
- `src/RepositoryInterface/Vendor/VendorPayoutRepositoryInterface.php`

## Security layer rule

`src/Security/` is forbidden for this component because it mixes code forms under a capability bucket.

Security code must be classified by Symfony/PHP type:

- security services belong under `src/Service/Security/`
- `src/Service/Sec/` and `src/ServiceInterface/Sec/` are forbidden aliases; use `Security` literally
- their mirrored contracts belong under `src/ServiceInterface/Security/`
- security DTOs belong under `src/DTO/...` and are not services
- authenticators must use `src/Authenticator/...`
- voters must use `src/Voter/...`
- subscribers must use `src/Subscriber/...`
- listeners must use `src/Listener/...`
- middleware must use `src/Middleware/...`

## Agent instruction

Before creating or moving code in the listed folders, check the exact table above.
Do not create domain/capability subfolders such as `Payout`, `Ledger`, `Ops`, `Finance`, `Statement`, `Integration`, `Metric`, `Dev`, or `VendorPayoutEntity` inside strict `Vendor`-only type folders.

For runtime HTTP entrypoints, derive the target from the route-map key:

```text
vendor.attachment.document.index
→ App\Vendoring\Service\Http\Vendor\Attachment\Document\VendorAttachmentDocumentIndexService
```

## EntityInterface literal addendum

`src/EntityInterface/` follows the same strict Layer 3 rule as `src/Entity/`.

Allowed:

- `src/EntityInterface/Vendor/Vendor*EntityInterface.php`
- namespace `App\Vendoring\EntityInterface\Vendor`

Forbidden:

- PHP files directly under `src/EntityInterface/`
- any direct child folder under `src/EntityInterface/` other than `Vendor`
- names like `VendorInterface.php`, `VendorSecurityInterface.php`, `VendorTransactionInterface.php`
- namespace `App\Vendoring\EntityInterface` for entity contracts

Entity contracts must not use a shorter `*Interface` name. They must use the full `*EntityInterface` suffix so machines cannot confuse entity contracts with service, policy, repository, event, or runtime-service contracts.

## Policy literal addendum

`src/Policy/` and `src/PolicyInterface/` follow the same strict Layer 3 rule as event, repository, and entity-interface layers.

Allowed:

- `src/Policy/Vendor/Vendor*Policy.php`
- namespace `App\Vendoring\Policy\Vendor`
- `src/PolicyInterface/Vendor/Vendor*PolicyInterface.php`
- namespace `App\Vendoring\PolicyInterface\Vendor`

Forbidden:

- PHP files directly under `src/Policy/` or `src/PolicyInterface/`
- any direct child folder under either layer other than `Vendor`
- names like `CategorySyndicationMappingPolicy.php` without the `Vendor` prefix
- namespace `App\Vendoring\Policy` or `App\Vendoring\PolicyInterface` for concrete policy classes/contracts

Policy classes are type-identifiable classes, not service-direction buckets. Machines must not place policy code in `src/Service/Policy/` only to satisfy autowiring; policy code belongs in the Policy layer and may still be autowired by Symfony through service discovery.

## Observability bucket addendum

`src/Observability/` is forbidden for this component because it mixes code forms under a capability bucket.
