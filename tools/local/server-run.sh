#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-18000}"

mkdir -p "$PROJECT_ROOT/var/log" "$PROJECT_ROOT/var/run"
cd "$PROJECT_ROOT"

exec php -S "${HOST}:${PORT}" -t public public/index.php
