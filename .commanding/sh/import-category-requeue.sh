#!/bin/sh
set -e
DLQ=report/category-import-dlq.json
if [ ! -f "$DLQ" ]; then echo 'no dlq'; exit 0; fi
cat "$DLQ" | php bin/console category:import -
