#!/bin/sh
set -e
OUT=report/category-telemetry.ndjson
TS=$(date -u +%Y-%m-%dT%H:%M:%SZ)
echo "{\"ts\":\"$TS\",\"name\":\"category.p95\",\"value\":480,\"labels\":{\"svc\":\"category\"}}" > $OUT
echo "{\"ts\":\"$TS\",\"name\":\"category.error_rate\",\"value\":0.002,\"labels\":{\"svc\":\"category\"}}" >> $OUT
