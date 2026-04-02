#!/usr/bin/env bash
set -euo pipefail

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/common.sh"

PROJECT_ROOT="$(local_server_project_root)"
PORT="$(local_server_port)"
PID_FILE="$(local_server_pid_file "$PROJECT_ROOT" "$PORT")"
PID="$(local_server_read_pid "$PID_FILE")"

if [[ -z "$PID" ]]; then
  echo "Local server is not running."
  exit 0
fi

if local_server_pid_is_running "$PID"; then
  kill "$PID"
fi

rm -f "$PID_FILE"
echo "Local server stopped."
