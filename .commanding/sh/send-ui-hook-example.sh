#!/usr/bin/env bash
set -euo pipefail
request_file="${1:-}"
[ -n "$request_file" ] || { echo 'request file is required' >&2; exit 2; }
[ -f "$request_file" ] || { echo "request file not found: $request_file" >&2; exit 2; }
echo "Example hook received request: $request_file"
echo 'Implement browser/UI attachment logic in your system-specific hook.'
exit 2
