#!/bin/sh
set -e
FILE=report/category-slo-current.json
if [ ! -f "$FILE" ]; then echo '{"error":"no slo file"}' > report/category-slo-ci.json; exit 1; fi
P95=$(python - <<'PY'
import json
d=json.load(open('report/category-slo-current.json'))
print(d.get('p95_ms', 9999))
PY
)
ERR=$(python - <<'PY'
import json
d=json.load(open('report/category-slo-current.json'))
print(d.get('error_rate', 1))
PY
)
STATUS=ok
if [ "$P95" -gt 700 ] || python - <<'PY'
import sys
err=float(sys.argv[1]); sys.exit(0 if err<=0.005 else 1)
PY
 "$ERR"; then
  STATUS=fail
fi
echo '{"p95":'$P95',"error_rate":'$ERR',"status":"'$STATUS'"}' > report/category-slo-ci.json
test "$STATUS" = "ok"
