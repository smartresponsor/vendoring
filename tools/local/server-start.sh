#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-18000}"
PID_FILE="$PROJECT_ROOT/var/run/local-server-${PORT}.pid"
LOG_FILE="$PROJECT_ROOT/var/log/local-server-${PORT}.log"
HEALTH_URL="http://${HOST}:${PORT}/healthz"

mkdir -p "$PROJECT_ROOT/var/log" "$PROJECT_ROOT/var/run"

if [[ -f "$PID_FILE" ]]; then
  PID="$(cat "$PID_FILE")"
  if kill -0 "$PID" >/dev/null 2>&1; then
    echo "Local server already running on ${HOST}:${PORT} (pid ${PID})."
    exit 0
  fi

  rm -f "$PID_FILE"
fi

if command -v setsid >/dev/null 2>&1; then
  setsid "$PROJECT_ROOT/tools/local/server-run.sh" >"$LOG_FILE" 2>&1 </dev/null &
else
  nohup "$PROJECT_ROOT/tools/local/server-run.sh" >"$LOG_FILE" 2>&1 </dev/null &
fi
PID=$!
echo "$PID" >"$PID_FILE"

for _ in $(seq 1 50); do
  RESPONSE="$(curl -fsS "$HEALTH_URL" 2>/dev/null || true)"
  if [[ "$RESPONSE" == *'"status":"ok"'* ]]; then
    echo "Local server started on ${HOST}:${PORT} (pid ${PID})."
    exit 0
  fi

  sleep 0.2
done

echo "Local server failed to become ready; see ${LOG_FILE}."
exit 1
