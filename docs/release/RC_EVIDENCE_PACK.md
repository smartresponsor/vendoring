# RC Evidence Pack

Generated through `composer docs:rc-evidence` and validated by `composer test:rc-evidence`.

## Evidence goals

The RC evidence pack exists to make release-candidate claims inspectable.
It should answer four practical questions:
- which repository-owned documentation surfaces are present
- which generated artifacts were emitted for this slice
- which release/runtime/operator docs are expected for RC review
- whether release and rollback manifests can be regenerated deterministically

## Generated artifacts

The evidence generation lane writes:
- `build/release/rc-evidence.json`
- `build/release/rc-evidence.md`
- `build/release/release-manifest.json`
- `build/release/release-manifest.md`
- `build/release/rollback-manifest.json`
- `build/release/rollback-manifest.md`
- `build/docs/runtime/index.txt`

## Evidence contents

The generated evidence includes at minimum:
- producer/documentation surface inventory
- generated OpenAPI/phpDocumentor/release-artifact presence checks
- release-doc presence checks
- grouped composer lanes relevant to RC review
- generation timestamp and repository-local provenance

## Boundary rule

The RC evidence pack is repository-local release proof.
It does not replace central release governance and does not assemble a global documentation portal.
