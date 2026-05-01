# Vendoring Layer 3 Structure and Naming Canon

This file is machine-readable operational canon for agents and code generators. Do not paraphrase it when editing code.

## Locked literal rule

For the Vendoring component, these Layer 3 source folders are locked to a single Vendor bucket:

- `src/Controller/Vendor/`
- `src/ControllerInterface/Vendor/` when controller interfaces exist
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
| `src/Controller/` | `Vendor` | `Vendor*Controller.php` | `App\Vendoring\Controller\Vendor` |
| `src/ControllerInterface/` | `Vendor` | `Vendor*ControllerInterface.php` | `App\Vendoring\ControllerInterface\Vendor` |
| `src/Event/` | `Vendor` | `Vendor*Event.php` | `App\Vendoring\Event\Vendor` |
| `src/EventInterface/` | `Vendor` | `Vendor*EventInterface.php` | `App\Vendoring\EventInterface\Vendor` |
| `src/Policy/` | `Vendor` | `Vendor*Policy.php` | `App\Vendoring\Policy\Vendor` |
| `src/PolicyInterface/` | `Vendor` | `Vendor*PolicyInterface.php` | `App\Vendoring\PolicyInterface\Vendor` |
| `src/Repository/` | `Vendor` | `Vendor*Repository.php` | `App\Vendoring\Repository\Vendor` |
| `src/RepositoryInterface/` | `Vendor` | `Vendor*RepositoryInterface.php` | `App\Vendoring\RepositoryInterface\Vendor` |
| `src/Projection/` | `Vendor` | `Vendor*Projection.php` | `App\Vendoring\Projection\Vendor` |

## Forbidden examples

These paths are forbidden even when the class name looks meaningful:

- `src/Controller/Payout/PayoutController.php`
- `src/Controller/Ops/VendorRuntimeStatusController.php`
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

- `src/Controller/Vendor/VendorPayoutController.php`
- `src/Event/Vendor/VendorPayoutCreatedEvent.php`
- `src/Event/Vendor/VendorCategorySyndicationPublishPackageBuiltEvent.php`
- `src/EventInterface/Vendor/VendorPayloadEventInterface.php`
- `src/Policy/Vendor/VendorCategorySyndicationMappingPolicy.php`
- `src/PolicyInterface/Vendor/VendorCategorySyndicationMappingPolicyInterface.php`
- `src/Repository/Vendor/VendorLedgerEntryRepository.php`
- `src/RepositoryInterface/Vendor/VendorPayoutRepositoryInterface.php`

## Security layer rule

`src/Security/` is forbidden for mixed security buckets in this component.

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

A class that must be autowired as a service belongs in `src/Service/<direction>/` or another Symfony type layer, not in `src/Security/`.

## Service exception

This canon does not impose the Vendor-only naming bucket on `src/Service/` and `src/ServiceInterface/`.
Those layers have their own direction-based rules. `src/Service/Security/` is currently valid because it contains autowired security services and has mirrored contracts in `src/ServiceInterface/Security/`.

## Agent instruction

Before creating or moving code in the listed folders, check the exact table above.
Do not create domain/capability subfolders such as `Payout`, `Ledger`, `Ops`, `Finance`, `Statement`, `Integration`, `Metric`, `Dev`, or `VendorPayoutEntity` inside the listed Layer 3 folders.

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

Entity contracts must not use a shorter `*Interface` name. They must use the full `*EntityInterface` suffix so machines cannot confuse entity contracts with service, policy, repository, event, or controller contracts.



## Policy literal addendum

`src/Policy/` and `src/PolicyInterface/` follow the same strict Layer 3 rule as controller, event, repository, and entity-interface layers.

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

Observability code must be classified by Symfony/PHP type:

- autowired observability services belong under `src/Service/Observability/`
- observability service files must use `Vendor*Service.php`
- observability service classes must use `Vendor*Service`
- observability service contracts belong under `src/ServiceInterface/Observability/`
- observability service contract files must use `Vendor*ServiceInterface.php`
- Symfony event subscribers belong under `src/Subscriber/...`, not under `src/Observability/EventSubscriber/`
- observability subscribers must use `Vendor*Subscriber.php`
- namespace must remain `App\Vendoring\...`

Forbidden:
- `src/Observability/Service/RuntimeLogger.php`
- `src/Observability/Service/MetricEmitter.php`
- `src/Observability/EventSubscriber/CorrelationIdSubscriber.php`

Canonical replacements:
- `src/Service/Observability/VendorRuntimeLoggerService.php`
- `src/Service/Observability/VendorMetricEmitterService.php`
- `src/Subscriber/Observability/VendorCorrelationIdSubscriber.php`

## Command support type-layer canon

`src/Command` is reserved for Symfony console command classes whose files/classes end with `Command`.

The mixed bucket `src/Command/Support` is forbidden. Command helper code must be classified by PHP type:

- command helper services: `src/Service/Command/Vendor*Service.php`
- command helper service interfaces: `src/ServiceInterface/Command/Vendor*ServiceInterface.php`
- command exceptions: `src/Exception/Command/Vendor*Exception.php`
- command DTO/input carriers: `src/DTO/Command/Vendor*DTO.php`
- command enum/value constants: `src/Enum/**/Vendor*Enum.php` with class `Vendor*Enum`

All files in those command helper layers must keep the component namespace prefix `App\\Vendoring\\...`.

## Command root and runtime helper addendum

`src/Command/` is allowed only for real Symfony console command entrypoints.

Allowed command files/classes:
- `src/Command/Vendor*Command.php`
- `final class Vendor*Command extends Symfony\\Component\\Console\\Command\\Command`
- `#[AsCommand(...)]` or equivalent Symfony command registration
- namespace `App\\Vendoring\\Command`

Forbidden command files/classes:
- `src/Command/CategoryReviewAssignCommand.php`
- `src/Command/SendVendorStatementsCommand.php`
- any command class without the `Vendor` prefix
- any helper/support/interface/DTO/exception code under `src/Command/Support/`

Runtime helper code must not live in `src/Support/` as an untyped bucket.

Allowed runtime helper service:
- `src/Service/Runtime/VendorAppEnvResolverService.php` implements `src/ServiceInterface/Runtime/VendorAppEnvResolverServiceInterface.php`
- consumers type against `App\\Vendoring\\ServiceInterface\\Runtime\\VendorAppEnvResolverServiceInterface`

Forbidden runtime helper:
- `src/Support/AppEnvResolver.php`
- namespace `App\\Vendoring\\Support`

Exception classes must be type-identifiable and component-prefixed:
- allowed: `src/Exception/Api/Vendor*Exception.php`
- forbidden: `src/Exception/ApiQueryValidationException.php`

The component namespace guardrail is strict: keep `App\\Vendoring\\...`; never flatten to old `App\\...`.


### DTO / Form / ValueObject literal naming canon

- `src/DTO/**` contains DTO classes only. File/class names must match `Vendor*DTO.php` / `Vendor*DTO`.
- `src/Form/**` contains Symfony form classes only. File/class names must match `Vendor*Form.php` / `Vendor*Form`. Symfony form builder semantics remain unchanged; the filename/class suffix is repository-canonicalized as `Form` to keep the layer type-identifiable.
- Form input/data carriers are DTOs and belong under `src/DTO/...`, not under `src/Form/...`.
- `src/ValueObject/**` contains value object classes only. File/class names must match `Vendor*ValueObject.php` / `Vendor*ValueObject`.
- The component namespace remains `App\\Vendoring\\...`; never flatten to the old `App\\...` namespace.


## Service / ServiceInterface direction-folder canon

`src/Service/` and `src/ServiceInterface/` are direction-based type layers.

Allowed:
- `src/Service/<Direction>/Vendor*Service.php`
- `src/ServiceInterface/<Direction>/Vendor*ServiceInterface.php`
- namespace `App\Vendoring\Service\<Direction>`
- namespace `App\Vendoring\ServiceInterface\<Direction>`

Forbidden:
- PHP files directly under `src/Service/`
- PHP files directly under `src/ServiceInterface/`
- service files without the `Vendor` prefix
- service files without the `Service.php` suffix
- service interface files without the `Vendor` prefix
- service interface files without the `ServiceInterface.php` suffix
- root namespaces such as `App\Vendoring\Service\VendorBillingService`
- root namespaces such as `App\Vendoring\ServiceInterface\VendorBillingServiceInterface`

Canonical examples:
- `src/Service/Billing/VendorBillingService.php`
- `src/ServiceInterface/Billing/VendorBillingServiceInterface.php`
- `src/Service/Profile/VendorProfileService.php`
- `src/ServiceInterface/Profile/VendorProfileServiceInterface.php`
- `src/Service/Transaction/VendorTransactionLifecycleService.php`
- `src/ServiceInterface/Transaction/VendorTransactionLifecycleServiceInterface.php`

The component namespace guardrail remains strict: keep `App\Vendoring\...`; never flatten to old `App\...`.

