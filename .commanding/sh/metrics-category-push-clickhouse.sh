#!/bin/sh
set -e
TS=$(date -Iseconds)
echo '{"ts":"'"$TS"'","api":1200,"admin":300}' > report/category-clickhouse-export.json
