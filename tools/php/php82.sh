#!/usr/bin/env bash
set -euo pipefail

if [[ -n "${PHP82_BINARY:-}" ]]; then
  exec "$PHP82_BINARY" "$@"
fi

if [[ -x /usr/bin/php8.2 ]]; then
  exec /usr/bin/php8.2 "$@"
fi

if command -v php8.2 >/dev/null 2>&1; then
  exec "$(command -v php8.2)" "$@"
fi

exec php "$@"
