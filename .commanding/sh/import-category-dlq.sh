#!/bin/sh
set -e
DLQ=report/category-import-dlq.json
echo '[]' > "$DLQ"
while read -r line; do
  if echo "$line" | grep -q 'ERROR'; then
    python - <<'PY'
import json,sys
dlq=json.load(open('report/category-import-dlq.json'))
dlq.append({'line':sys.argv[1]})
json.dump(dlq, open('report/category-import-dlq.json','w'))
PY
  fi
done < "${1:-/dev/stdin}"
