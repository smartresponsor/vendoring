#!/usr/bin/env bash
set -euo pipefail

# Export a log snapshot for review.
# Output goes to report/git-log/ (ignored).

if ! command -v git >/dev/null 2>&1; then
  echo "ERROR: git is not installed or not in PATH." >&2
  exit 1
fi

if ! git rev-parse --git-dir >/dev/null 2>&1; then
  echo "ERROR: run this script from inside a git repository." >&2
  exit 1
fi

OUT_DIR="report/git-log"
mkdir -p "${OUT_DIR}"

STAMP="$(date +%Y%m%d-%H%M%S)"

git log --graph --decorate --oneline --all > "${OUT_DIR}/log-graph-${STAMP}.txt"

git log --decorate --date=iso --pretty=format:'%H %ad %D%n%s%n' --stat --all > "${OUT_DIR}/log-stat-${STAMP}.txt"

echo "Wrote: ${OUT_DIR}/log-graph-${STAMP}.txt"
echo "Wrote: ${OUT_DIR}/log-stat-${STAMP}.txt"
