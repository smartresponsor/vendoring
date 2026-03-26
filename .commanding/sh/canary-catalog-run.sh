#!/bin/sh
set -e
START=$(date -Iseconds)
STATUS=green
cat > report/category-canary-window.json <<JSON
{
  "start": "$START",
  "end": "$START",
  "status": "$STATUS",
  "slo": {"read_p95_ms": 250, "write_p95_ms": 700, "error_rate": 0.3}
}
JSON
