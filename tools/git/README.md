# Git history + fast-import hygiene (Vendoring)

This repo has accumulated multiple "phase" snapshots and fast-import artifacts inside runtime paths (especially under `src/`).
Those artifacts must not live under autoloaded or runtime directories.

Goals:
- Keep runtime code under `src/` clean (PSR-4 friendly).
- Quarantine phase snapshots and fast-import streams outside runtime paths.
- Provide a repeatable way to rewrite history (optional) if you decide to permanently drop that baggage.

What belongs where:
- Runtime code: `src/`, `config/`, `migrations/`, `public/`.
- Quarantine (not autoloaded): `.archive/`.
- Local-only exports/reports: `report/` (ignored).

Recommended approach:
1) First, move/remove the obvious runtime-busting paths (see the Canon scanner / guards).
2) If you want a clean public history, run `git filter-repo` using the provided scripts.

Scripts:
- `vendor-filter-repo.sh` / `vendor-filter-repo.ps1` — remove known-bad paths from history.
- `vendor-log-export.sh` / `vendor-log-export.ps1` — export a log snapshot for review.

Notes:
- History rewrite is destructive. Use a fresh clone and push to a new branch/repo.
- Keep tags only if they point to deployable commits.
