#!/bin/sh
set -e
FILE=${1:-report/category-backup-latest.ndjson}
if [ ! -f "$FILE" ]; then echo "no backup"; exit 1; fi
cp "$FILE" report/category-restore.log
