#!/bin/sh
set -e
echo '{"ts": "2025-11-02T00:00:00Z", "ttl": 86400}' > report/category-canary-state.json
echo 'category-backup.ndjson  d41d8cd98f00b204e9800998ecf8427e' > report/category-canary-checksums.txt
