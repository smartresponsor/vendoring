#!/usr/bin/env bash
set -euo pipefail

# Vendoring: history cleanup (remove phase snapshots / fast-import baggage)
# Requires: git + git-filter-repo

if ! command -v git >/dev/null 2>&1; then
  echo "ERROR: git is not installed or not in PATH." >&2
  exit 1
fi

if ! git rev-parse --git-dir >/dev/null 2>&1; then
  echo "ERROR: run this script from inside a git repository." >&2
  exit 1
fi

if ! command -v git-filter-repo >/dev/null 2>&1; then
  echo "ERROR: git-filter-repo is not installed." >&2
  echo "Install: pipx install git-filter-repo  (or: pip install git-filter-repo)" >&2
  exit 1
fi

# Safety: refuse to run on a dirty working tree.
if ! git diff --quiet || ! git diff --cached --quiet; then
  echo "ERROR: working tree is not clean. Commit/stash changes first." >&2
  exit 1
fi

# Optional: create a safety tag before rewriting.
SAFETY_TAG="vendor-history-pre-filter-$(date +%Y%m%d-%H%M%S)"
if ! git show-ref --tags --quiet --verify "refs/tags/${SAFETY_TAG}"; then
  git tag "${SAFETY_TAG}" || true
fi

echo "Rewriting history... (safety tag: ${SAFETY_TAG})"

# Remove known-bad paths. Keep everything else.
# Tip: adjust globs if you renamed folders earlier.

git filter-repo --force \
  --invert-paths \
  --path-glob 'src/src/**' \
  --path-glob 'src/**/src/**' \
  --path-glob 'src/**/vendor-current/**' \
  --path-glob 'src/**/vendor-sketch*/**' \
  --path-glob 'src/**/vendor-phase*/**' \
  --path-glob 'src/**/vendor-bucket*/**' \
  --path-glob 'src/**/[0-9][0-9][0-9]_*vendor-*/**' \
  --path-glob 'src/**/[0-9][0-9][0-9]-vendor-*/**' \
  --path-glob '.commanding/legacy/fast-import/**'

echo "Done. Review the repository, then force-push to a NEW branch or NEW remote."
