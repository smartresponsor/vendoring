#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PORT="${PORT:-18000}"
PID_FILE="$PROJECT_ROOT/var/run/local-server-${PORT}.pid"

if [[ ! -f "$PID_FILE" ]]; then
  echo "Local server is not running."
  exit 0
fi

PID="$(cat "$PID_FILE")"

if kill -0 "$PID" >/dev/null 2>&1; then
  kill "$PID"
fi

rm -f "$PID_FILE"
echo "Local server stopped."
