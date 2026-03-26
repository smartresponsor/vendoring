#!/bin/sh
set -e
cat > report/category-perf-profile.json <<'JSON'
{
  "api_storefront": {"p50": 40, "p95": 110},
  "api_search": {"p50": 60, "p95": 150},
  "admin_list": {"p50": 80, "p95": 200}
}
JSON
