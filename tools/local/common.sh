#!/usr/bin/env bash

local_server_default_host() {
  printf '%s\n' '127.0.0.1'
}

local_server_default_port() {
  printf '%s\n' '18000'
}

local_server_project_root() {
  cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd
}

local_server_host() {
  printf '%s\n' "${HOST:-$(local_server_default_host)}"
}

local_server_port() {
  printf '%s\n' "${PORT:-$(local_server_default_port)}"
}

local_server_pid_file() {
  local project_root="$1"
  local port="$2"

  printf '%s\n' "$project_root/var/run/local-server-${port}.pid"
}

local_server_log_file() {
  local project_root="$1"
  local port="$2"

  printf '%s\n' "$project_root/var/log/local-server-${port}.log"
}

local_server_health_url() {
  local host="$1"
  local port="$2"

  printf 'http://%s:%s/healthz\n' "$host" "$port"
}

local_server_prepare_runtime_dirs() {
  local project_root="$1"

  mkdir -p "$project_root/var/log" "$project_root/var/run"
}

local_server_read_pid() {
  local pid_file="$1"

  if [[ -f "$pid_file" ]]; then
    cat "$pid_file"
  fi
}

local_server_pid_is_running() {
  local pid="$1"

  [[ -n "$pid" ]] && kill -0 "$pid" >/dev/null 2>&1
}
