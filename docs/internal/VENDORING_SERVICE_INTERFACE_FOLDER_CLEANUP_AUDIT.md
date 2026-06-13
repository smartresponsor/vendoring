# Vendoring ServiceInterface Folder Cleanup Audit

## Scope

This wave cleans the remaining `ServiceInterface` folder mismatches after the projection and mirror-contract canonization waves.

## Findings

- `src/Service/Category/Rule/VendorCategoryRuleEngineService.php` lived under `Service/Category/Rule`, but its contract lived directly under `ServiceInterface/Category`.
- `src/Service/Category/Suggest/VendorCategoryRuleSuggestService.php` lived under `Service/Category/Suggest`, but its contract lived directly under `ServiceInterface/Category`.
- `src/ServiceInterface/Acl/VendorAclRepositoryServiceInterface.php` and `src/ServiceInterface/Rule/VendorRuleRepositoryServiceInterface.php` were orphan repository-style contracts without matching concrete services or references in the component graph.

## Canonical result

- Category rule contracts now mirror their implementation capability path:
  - `src/ServiceInterface/Category/Rule/VendorCategoryRuleEngineServiceInterface.php`
  - `src/ServiceInterface/Category/Suggest/VendorCategoryRuleSuggestServiceInterface.php`
- Implementations import the mirrored contracts from the matching subfolders.
- `config/component/services.yaml` binds the new canonical interface FQCNs to the existing implementations.
- Unused orphan repository-style service contracts are removed from the cumulative tree.

## Intentionally not changed

- Concrete helper services that are directly instantiated by commands were not forced into artificial interfaces in this wave.
- Runtime/static utility service shape was not changed.
- Namespace remains `App\Vendoring\...`.
