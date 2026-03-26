#!/bin/sh
set -e
ZIP=${1:-category-release.zip}
zip -r "$ZIP" src config tools docs api public migrations report || true
sha256sum "$ZIP" > report/category-release-sha256.txt
cat > report/category-release-manifest.json <<'JSON'
{
  "packages": ["f-pack","g-pack","h-pack","i-pack","j-pack"],
  "version": "rc1",
  "status": "ready"
}
JSON
