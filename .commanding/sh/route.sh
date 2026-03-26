#!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

SELF_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
COMMANDING_DIR="${COMMANDING_DIR:-$(cd -- "$SELF_DIR/.." && pwd)}"

# shellcheck source=/dev/null
source "$COMMANDING_DIR/lib/ui.sh"

ensure_console() {
  local target console
  target="$(resolve_target_root)"
  console="$(target_console_bin)"

  if [ ! -f "$console" ]; then
    ui_error "bin/console not found in target root: $target"
    return 1
  fi

  if [ ! -r "$console" ]; then
    ui_error "bin/console is not readable: $console"
    return 1
  fi

  return 0
}

contains_html() {
  grep -Eiq '<!doctype html>|<html[[:space:]>]|<body[[:space:]>]|<head[[:space:]>]'
}

run_router_command() {
  local label="$1"
  shift

  local target output status
  target="$(resolve_target_root)"

  if ! ensure_console; then
    return 1
  fi

  log_action "route: $label (target=$target)"

  set +e
  output="$(cd "$target" && php "$(target_console_bin)" debug:router --no-ansi "$@" 2>&1)"
  status=$?
  set -e

  if printf '%s' "$output" | contains_html; then
    ui_error "Route command produced HTML/web output instead of CLI output."
    printf '%s\n' "$output"
    return 1
  fi

  printf '%s\n' "$output"
  return $status
}

prompt_value() {
  local label="$1"
  local value=''
  printf '%s' "$label"
  IFS= read -r value || true
  printf '%s' "$value"
}

list_all_routes() {
  run_router_command 'list all routes'
}

show_route_detail() {
  local name
  name="$(prompt_value 'Route name: ')"
  if [ -z "$name" ]; then
    ui_warn 'Route name is required.'
    return 0
  fi
  run_router_command "show route detail: $name" "$name"
}

filter_partial() {
  local fragment
  fragment="$(prompt_value 'Partial route name: ')"
  if [ -z "$fragment" ]; then
    ui_warn 'Partial route name is required.'
    return 0
  fi
  local output status
  set +e
  output="$(run_router_command "filter route by partial name: $fragment" 2>&1)"
  status=$?
  set -e
  [ $status -eq 0 ] || { printf '%s\n' "$output"; return $status; }
  printf '%s\n' "$output" | awk -v q="$fragment" 'NR==1 || index($0, q) > 0'
}

filter_exact() {
  local name
  name="$(prompt_value 'Exact route name: ')"
  if [ -z "$name" ]; then
    ui_warn 'Exact route name is required.'
    return 0
  fi
  run_router_command "filter route by exact name: $name" "$name"
}

open_action_log() {
  local file
  file="$(runtime_log_file)"

  if [ ! -f "$file" ]; then
    ui_warn "Action log not found: $file"
    return 0
  fi

  show_file "$file"
}

print_menu() {
  ui_clear
  ui_banner 'Routes'
  printf '%s\n' '1) List all routes'
  printf '%s\n' '2) Show route detail by name'
  printf '%s\n' '3) Filter by partial name'
  printf '%s\n' '4) Filter by exact name'
  printf '%s\n' '5) Open action log'
  printf '\n'
  printf '%s\n' 'Space) Back'
}

menu_loop() {
  local choice=''
  while true; do
    print_menu
    printf '%s' 'Choice: '
    choice="$(ui_read_choice || true)"
    printf '\n\n'
    case "$choice" in
      ''|' ') break ;;
      1) list_all_routes ;;
      2) show_route_detail ;;
      3) filter_partial ;;
      4) filter_exact ;;
      5) open_action_log ;;
      *) ui_warn "Unknown choice: $choice" ;;
    esac
    printf '\n'
    ui_pause_any
  done
}

main() {
  case "${1:-}" in
    --self-check)
      ensure_console
      ;;
    --list)
      list_all_routes
      ;;
    --detail)
      shift
      run_router_command "show route detail: ${1:-}" "${1:-}"
      ;;
    *)
      menu_loop
      ;;
  esac
}

main "$@"
