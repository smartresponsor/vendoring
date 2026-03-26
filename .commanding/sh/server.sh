#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
export COMMANDING_DIR
source "$COMMANDING_DIR/lib/ui.sh"
TARGET_ROOT="$(resolve_target_root)"
CONSOLE_BIN="$(target_console_bin)"

require_runtime() {
  local missing=0
  command_exists symfony || { ui_error "Symfony CLI is not available in PATH"; missing=1; }
  command_exists php || { ui_error "PHP is not available in PATH"; missing=1; }
  [ -f "$CONSOLE_BIN" ] || { ui_error "bin/console not found in target root: $TARGET_ROOT"; missing=1; }
  [ "$missing" -eq 0 ]
}

run_server_action() {
  local label="$1"
  shift || true
  run_logged_in_target "$label" "$@"
}

server_restart() {
  ui_note "Restarting Symfony local server for target root..."
  run_server_action "Server stop" symfony server:stop
  run_server_action "Server start detached" symfony server:start -d --dir "$TARGET_ROOT" --no-tls
}

server_restart_with_cache() {
  ui_note "Restarting server and refreshing cache for target root..."
  run_server_action "Server stop" symfony server:stop
  run_server_action "Cache clear" php "$CONSOLE_BIN" cache:clear
  run_server_action "Cache warmup dev" php "$CONSOLE_BIN" cache:warmup --env=dev
  run_server_action "Server start detached" symfony server:start -d --dir "$TARGET_ROOT" --no-tls
}

server_restart_with_schema() {
  ui_warn "This action updates the database schema with --force."
  if ! ui_confirm "Continue with schema update? [y/N]: "; then
    ui_note "Cancelled."
    return 0
  fi
  ui_note "Restarting server and applying schema update for target root..."
  run_server_action "Server stop" symfony server:stop
  run_server_action "Schema update force" php "$CONSOLE_BIN" doctrine:schema:update --complete --force
  run_server_action "Schema validate" php "$CONSOLE_BIN" doctrine:schema:validate
  run_server_action "Server start detached" symfony server:start -d --dir "$TARGET_ROOT" --no-tls
}

print_menu() {
  ui_clear
  ui_banner "Server"
  printf '%s
' "Server Menu" "-----------" "1) Restart server" "2) Restart + cache clear + warmup" "3) Restart + schema update + validate" "4) Open action log" "Space) Exit"
}

main() {
  if ! require_runtime; then
    ui_pause_any
    exit 0
  fi

  while true; do
    print_menu
    printf '%s' 'Choice: '
    action="$(ui_read_choice || true)"
    printf '

'

    status=0
    case "${action:-}" in
      ''|' '|0|q|Q) exit 0 ;;
      1) server_restart || status=$? ;;
      2) server_restart_with_cache || status=$? ;;
      3) server_restart_with_schema || status=$? ;;
      4) show_file "$(runtime_log_file)" || status=$? ;;
      *) ui_warn "Unknown action: ${action:-}"; ui_pause_any; continue ;;
    esac

    [ "${action:-}" = '4' ] && continue
    ui_complete_action "$status"
  done
}

main "$@"
