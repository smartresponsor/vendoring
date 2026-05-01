Contract checks validate *repository invariants*.

Scope:
- Root contract: only explicitly listed root files, explicitly listed application/tooling directories, and explicitly listed dot-folders are allowed.
- .gitignore must match the consumer template requirements.

This folder must NOT contain linting checks (naming/style). Those belong to `.gate/linting/`.

Current root file contract:

- Required root files: `.gitignore`, `README.md`, `composer.json`.
- Root-level `MANIFEST.json` is not required for Vendoring; gate manifests live under `.gate/`.
- Allowed non-dot root directories are enumerated in `contract.json` and must stay intentional.


Layer 3 source contract:
- `src/Controller/`, `src/ControllerInterface/`, `src/Event/`, `src/EventInterface/`, `src/Policy/`, `src/PolicyInterface/`, `src/Repository/`, `src/Projection/`, and `src/RepositoryInterface/` may contain only the direct child folder `Vendor`.
- Files inside those Vendor folders must match the literal layer suffix: `Vendor*Controller.php`, `Vendor*ControllerInterface.php`, `Vendor*Event.php`, `Vendor*EventInterface.php`, `Vendor*Policy.php`, `Vendor*PolicyInterface.php`, `Vendor*Repository.php`, `Vendor*RepositoryInterface.php`, and `Vendor*Projection.php`.
- `src/Security/` is forbidden; classify security code by type, with autowired security services under `src/Service/Security/` and mirrored contracts under `src/ServiceInterface/Security/`.

EntityInterface is locked to `src/EntityInterface/Vendor/Vendor*EntityInterface.php`; root `src/EntityInterface/*.php` and shorter `Vendor*Interface.php` names are contract violations.

Policy layer is locked to `src/Policy/Vendor/Vendor*Policy.php` and `src/PolicyInterface/Vendor/Vendor*PolicyInterface.php`. Root policy files and non-Vendor policy names are violations.

### Observability type-layer rule

`src/Observability/` is forbidden. Observability services must live in `src/Service/Observability/` as `Vendor*Service.php`; observability service interfaces must live in `src/ServiceInterface/Observability/` as `Vendor*ServiceInterface.php`; event subscribers must live in `src/Subscriber/...` as `Vendor*Subscriber.php`.

### Command support canon

`src/Command/Support` is forbidden because it mixes services, interfaces, DTO/input carriers, enum-like values, and exceptions. Command support code must be sorted into type-identifiable layers: `Service/Command`, `ServiceInterface/Command`, `DTO/Command`, `Enum/Command`, and `Exception/Command`, with `Vendor` prefix and the proper suffix for the layer.

### Vendoring command/support naming canon

- `src/Command/` may contain only Symfony console commands named `Vendor*Command.php`.
- `src/Command/Support/` is forbidden.
- Generic `src/Support/` is forbidden.
- Runtime helpers belong under `src/Service/Runtime/Vendor*Service.php`.
- API exceptions belong under `src/Exception/Api/Vendor*Exception.php`.
- Namespace must remain `App\\Vendoring\\...`.


### DTO / Form / ValueObject literal naming canon

- `src/DTO/**` contains DTO classes only. File/class names must match `Vendor*DTO.php` / `Vendor*DTO`.
- `src/Form/**` contains Symfony form classes only. File/class names must match `Vendor*Form.php` / `Vendor*Form`. The class may still extend Symfony `AbstractType`; the file/class suffix stays `Form` for ecosystem type-layer readability.
- Form input/data carriers are DTOs and belong under `src/DTO/...`, not under `src/Form/...`.
- `src/ValueObject/**` contains value object classes only. File/class names must match `Vendor*ValueObject.php` / `Vendor*ValueObject`.
- The component namespace remains `App\\Vendoring\\...`; never flatten to the old `App\\...` namespace.

### Service / ServiceInterface direction folders

- `src/Service/` must not contain PHP files directly.
- `src/ServiceInterface/` must not contain PHP files directly.
- Service implementations must live as `src/Service/<Direction>/Vendor*Service.php`.
- Service contracts must live as `src/ServiceInterface/<Direction>/Vendor*ServiceInterface.php`.
- Keep `App\Vendoring\...`; do not flatten to `App\...`.


## Failure artifacts

Root-contract failure markers must be written under `build/reports/gate/`, not under a root-level `.report/` folder. The repository root contract does not allow `.report/`; gate tools must not create forbidden root residue while reporting a violation.
