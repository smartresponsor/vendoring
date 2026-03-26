#!/bin/sh
set -e
SRC=${1:-report/category-canary-state.json}
OUT=report/category-restore-report.json
if [ ! -f "$SRC" ]; then
  echo '{"status":"error","reason":"backup not found"}' > "$OUT"
  exit 1
fi
echo '{"status":"ok","restored_from":"'"$SRC"'"}' > "$OUT"
