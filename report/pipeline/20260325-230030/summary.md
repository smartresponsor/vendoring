# Vendoring local pipeline

- Timestamp: `20260325-230030`
- Report root: `D:\PhpstormProjects\www\Vendoring\report\pipeline\20260325-230030`
- Passed: **14**
- Non-passed: **4**
- Skipped: **2**
- Total: **20**

| Status | Step | Exit | Duration (s) | Log |
|---|---|---:|---:|---|
| passed | composer-validate | 0 | 1.423 | ``composer-validate.log`` |
| passed | lint | 0 | 44.329 | ``lint.log`` |
| failed | cs-check | 1 | 6.291 | ``cs-check.log`` |
| failed | phpstan | 1 | 3.791 | ``phpstan.log`` |
| skipped | phpmd-src | 0 | 0 | ``phpmd-src.log`` |
| skipped | phpmd-tests | 0 | 0 | ``phpmd-tests.log`` |
| failed | phpunit | 1 | 4.656 | ``phpunit.log`` |
| passed | smoke-runtime | 0 | 2.446 | ``smoke-runtime.log`` |
| passed | smoke-container | 0 | 2.366 | ``smoke-container.log`` |
| passed | smoke-doctrine | 0 | 1.859 | ``smoke-doctrine.log`` |
| passed | smoke-admin | 0 | 2.101 | ``smoke-admin.log`` |
| passed | report-canonical-structure | 0 | 2.459 | ``report-canonical-structure.log`` |
| passed | report-mirror-enforcer | 0 | 1.98 | ``report-mirror-enforcer.log`` |
| failed | report-config-guard | 1 | 1.935 | ``report-config-guard.log`` |
| passed | report-config-drift | 0 | 2.42 | ``report-config-drift.log`` |
| passed | report-php-surface | 0 | 45.642 | ``report-php-surface.log`` |
| passed | report-prod-marker | 0 | 2.346 | ``report-prod-marker.log`` |
| passed | report-quality-residue | 0 | 2.168 | ``report-quality-residue.log`` |
| passed | report-contract | 0 | 2.213 | ``report-contract.log`` |
| passed | report-readiness | 0 | 1.886 | ``report-readiness.log`` |
