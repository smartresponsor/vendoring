#!/usr/bin/env bash
set -euo pipefail

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/common.sh"

PROJECT_ROOT="$(local_server_project_root)"
HOST="$(local_server_host)"
PORT="$(local_server_port)"
PID_FILE="$(local_server_pid_file "$PROJECT_ROOT" "$PORT")"
LOG_FILE="$(local_server_log_file "$PROJECT_ROOT" "$PORT")"
HEALTH_URL="$(local_server_health_url "$HOST" "$PORT")"

local_server_prepare_runtime_dirs "$PROJECT_ROOT"

PID="$(local_server_read_pid "$PID_FILE")"

if local_server_pid_is_running "$PID"; then
  echo "Local server already running on ${HOST}:${PORT} (pid ${PID})."
  exit 0
fi

if [[ -n "$PID" ]]; then
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

rm -f "$PID_FILE"
echo "Local server failed to become ready; see ${LOG_FILE}."
exit 1
