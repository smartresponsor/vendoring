#!/bin/sh
set -e
OUT=report/category-backup-$(date +%Y%m%d%H%M%S).ndjson
echo '{"id":"1","name":"Root","slug":"root","parent":null}' > "$OUT"
jq -R -s -c 'split("\n")[:-1]' "$OUT" > report/category-backup-index.json
