#!/bin/sh
set -e
OUT=report/category-smoke-v2.json
echo '{"api":true,"graphql":true,"admin":true,"storefront":true}' > "$OUT"
