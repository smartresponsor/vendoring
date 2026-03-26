#!/bin/sh
set -e
OUT=report/category-tenant-health.json
cat > $OUT <<'JSON'
[
  {"tenant":"default","projections":"ok","quotas":"ok","webhook":"ok","dlq":0},
  {"tenant":"merchant-a","projections":"ok","quotas":"warn","webhook":"fail","dlq":3}
]
JSON
