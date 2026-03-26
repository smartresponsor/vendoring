#!/bin/sh
set -e
OUT=docs/category-all-in-one.md
echo '# Category docs (f..r)' > $OUT
for f in docs/*.md; do
  echo "\n## $f" >> $OUT
  cat "$f" >> $OUT
done
ls docs/*.md | jq -R -s -c 'split("\n")[:-1]' > docs/category-docs-index.json
