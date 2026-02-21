#!/usr/bin/env bash

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

set -euo pipefail

OUT="${1:-}"
PRUNE_EMPTY="${PRUNE_EMPTY:-0}"
STRICT="${STRICT:-0}"

REPO_ROOT="$(git rev-parse --show-toplevel 2>/dev/null || true)"
if [[ -z "$REPO_ROOT" ]]; then
  echo "ERROR: not a git repository" >&2
  exit 2
fi

cd "$REPO_ROOT"
HASH="$(git rev-parse --short HEAD)"
TS="$(date +%Y%m%dT%H%M)"

if [[ -z "$OUT" ]]; then
  OUT="$REPO_ROOT/vendoring-export-$TS-$HASH.zip"
fi

if [[ "$PRUNE_EMPTY" == "1" ]]; then
  php tools/vendoring-prune-empty-dir.php --root=src
fi

if [[ "$STRICT" == "1" ]]; then
  php tools/vendoring-structure-scan.php --strict
fi

git archive --format=zip -o "$OUT" HEAD

echo "OK: exported $OUT"
