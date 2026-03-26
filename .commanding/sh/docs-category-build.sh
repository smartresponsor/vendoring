#!/bin/sh
set -e
mkdir -p public/docs/category
cat > public/docs/category/index.html <<'HTML'
<!doctype html>
<title>Category Docs</title>
<h1>SmartResponsor Category</h1>
<ul>
  <li><a href="../../../../docs/category-ga-release-notes-final.md">Release Notes</a></li>
  <li><a href="../../../../api/category-openapi.yaml">OpenAPI</a></li>
</ul>
HTML
