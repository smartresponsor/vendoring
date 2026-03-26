#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/sh/health-lib.sh"

print_health_snapshot() {
  local latest
  latest="$(health_latest_run_dir || true)"
  printf '%s\n' "$(health_latest_status_line)"
  if [ -n "$latest" ]; then
    printf ' Latest logs: %s\n' "$latest"
  fi
}

open_latest_summary() {
  local latest
  latest="$(health_latest_run_dir || true)"
  if [ -z "$latest" ]; then
    ui_warn 'No health runs found yet.'
    return 1
  fi
  show_file "$latest/summary.txt"
}

open_latest_results() {
  local latest
  latest="$(health_latest_run_dir || true)"
  if [ -z "$latest" ]; then
    ui_warn 'No health runs found yet.'
    return 1
  fi
  show_file "$(health_results_file "$latest")"
}

while true; do
  ui_clear
  ui_banner "Health"
  print_health_snapshot
  printf '\n'
  printf '%s\n' \
    'Health Menu' \
    '-----------' \
    '1) Sandbox full sweep' \
    '2) Application limited sweep' \
    '3) Open latest summary' \
    '4) Open latest failures' \
    '5) Rerun latest failed probes' \
    '6) Open latest results table' \
    '7) Open a failed probe log' \
    '' \
    'Space) Exit'
  printf '%s' 'Choice: '
  action="$(ui_read_choice || true)"
  printf '\n\n'
  status=0
  case "${action:-}" in
    1)
      bash "$COMMANDING_DIR/sh/health-sandbox-sweep.sh" sandbox || status=$?
      ;;
    2)
      bash "$COMMANDING_DIR/sh/health-sandbox-sweep.sh" application || status=$?
      ;;
    3)
      open_latest_summary || status=$?
      ;;
    4)
      health_open_latest_failures || status=$?
      ;;
    5)
      bash "$COMMANDING_DIR/sh/health-rerun-failed.sh" "$(health_target_mode)" || status=$?
      ;;
    6)
      open_latest_results || status=$?
      ;;
    7)
      bash "$COMMANDING_DIR/sh/health-open-failed-log.sh" || status=$?
      ;;
    8)
      bash "$COMMANDING_DIR/sh/health-list-probes.sh" "$(health_target_mode)" || status=$?
      ;;
    9)
      bash "$COMMANDING_DIR/sh/health-run-selected.sh" "$(health_target_mode)" || status=$?
      ;;
    ''|' '|0|q|Q)
      exit 0
      ;;
    *)
      ui_warn "Unknown action: ${action:-}"
      ui_pause_any
      continue
      ;;
  esac
  ui_complete_action "$status"
done
