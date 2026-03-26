#!/usr/bin/env bash
set -euo pipefail

if [[ -n "${PHP84_BINARY:-}" ]]; then
  exec "$PHP84_BINARY" "$@"
fi

if [[ -x /usr/bin/php8.4 ]]; then
  exec /usr/bin/php8.4 "$@"
fi

if command -v php8.4 >/dev/null 2>&1; then
  exec "$(command -v php8.4)" "$@"
fi

exec php "$@"
