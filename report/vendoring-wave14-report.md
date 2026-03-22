# Vendoring Wave14 Report

Active base: cumulative snapshot wave13.

Change:
- Moved non-runtime policy artifact from `ops/policy/services_interface.yaml` to `ops/policy/config/services_interface.yaml`.

Why:
- `ops/policy/README.md` declares this tree as non-runtime policy documents.
- Wave11 already normalized policy config under `ops/policy/config/...`.
- Leaving `services_interface.yaml` at `ops/policy/` root kept one stray config-like artifact outside the normalized policy config subtree.
- No runtime `src/` code or Symfony-loaded `config/` files were changed.
