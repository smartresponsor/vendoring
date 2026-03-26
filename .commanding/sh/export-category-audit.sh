#!/bin/sh
set -e
SRC=report/category-telemetry.ndjson
OUT=report/category-audit-export.ndjson
if [ -f "$SRC" ]; then cp "$SRC" "$OUT"; else echo '' > "$OUT"; fi
echo '{"status":"ok","exported_from":"'"$SRC"'"}' > report/category-audit-index.json
