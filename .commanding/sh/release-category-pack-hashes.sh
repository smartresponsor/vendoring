#!/bin/sh
set -e
OUT=report/category-f..p-hashes.txt
echo "# category f..p" > "$OUT"
for pack in f g h i j k l m n o p; do
  FILE="/mnt/data/category-rc1-$pack-pack.zip"
  if [ -f "$FILE" ]; then
    sha256sum "$FILE" >> "$OUT"
  else
    echo "missing: $pack" >> "$OUT"
  fi
done
