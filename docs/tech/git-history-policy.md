# Git history policy (Vendor)

Target: keep the repository readable and maintainable.

Keep:
- Commits that introduce deployable behavior (features, fixes, schema changes).
- Release tags that point to deployable commits (RC/GA).
- One or two global style normalizations (if needed) — not hundreds.

Drop / rewrite away:
- Commits that only import phase snapshots into `src/`.
- Any commits that create recursive paths (`src/src`, `Controller/Controller`, `Entity/Entity`, etc.).
- Fast-import artifacts inside runtime paths.

Practical rules:
- Runtime code must never include phase snapshots (no `vendor-current`, no `###_vendor-*`).
- Phase snapshots belong in `.archive/` or outside the repo.
- If you must keep a fast-import stream, keep it in a quarantined area and do not autoload it.

How to rewrite (optional):
- Use `tools/git/vendor-filter-repo.(sh|ps1)` in a fresh clone.
- Push rewritten history to a NEW branch or NEW remote.
- Re-tag only meaningful release points.

Fast-import:
- Treat fast-import as a transport format, not as source code.
- If you keep it, store it outside runtime and ignore local copies via `.gitignore`.
