# Vendoring wave05

Base: wave04 cumulative snapshot.

Changes:
- removed empty residual legacy container directory: `docs/legacy/`

Rationale:
- after wave04, legacy Port/Adapter artifacts were already removed
- the remaining `docs/legacy/` directory was empty and served no runtime or documentation purpose
- removal reduces repository noise and future false-positive scans

Risk:
- none expected; no PHP source files changed
