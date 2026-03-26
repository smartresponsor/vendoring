#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
export COMMANDING_DIR
source "$COMMANDING_DIR/lib/ui.sh"
TARGET_ROOT="$(resolve_target_root)"
CONSOLE_BIN="$(target_console_bin)"
ensure_migration_runtime() { require_command php || return 1; require_file "$CONSOLE_BIN" || return 1; }
run_migration_diff() { ensure_migration_runtime || return 1; run_logged_in_target "migration diff" php "$CONSOLE_BIN" doctrine:migrations:diff; }
run_migration_migrate() { ensure_migration_runtime || return 1; if ! ui_confirm "Run doctrine:migrations:migrate? [y/N]: "; then ui_note "Migration cancelled."; return 0; fi; run_logged_in_target "migration migrate" php "$CONSOLE_BIN" doctrine:migrations:migrate --no-interaction; }
run_migration_rollback() { ensure_migration_runtime || return 1; if ! ui_confirm "Rollback to previous migration? [y/N]: "; then ui_note "Rollback cancelled."; return 0; fi; run_logged_in_target "migration rollback prev" php "$CONSOLE_BIN" doctrine:migrations:migrate prev --no-interaction; }
run_migration_status() { ensure_migration_runtime || return 1; run_logged_in_target "migration status" php "$CONSOLE_BIN" doctrine:migrations:status; }
run_migration_execute() { ensure_migration_runtime || return 1; local version=""; printf '%s' 'Enter migration version: '; IFS= read -r version || true; if [ -z "$version" ]; then ui_warn "Migration version is required."; return 1; fi; if ! ui_confirm "Execute migration ${version}? [y/N]: "; then ui_note "Execution cancelled."; return 0; fi; run_logged_in_target "migration execute $version" php "$CONSOLE_BIN" doctrine:migrations:execute "$version" --up --no-interaction; }
main() { while true; do ui_clear; ui_banner "Migration"; printf '%s
' "1) Create migration (diff)" "2) Apply migration" "3) Rollback to previous" "4) Show migration status" "5) Execute specific migration" "6) Open action log" "" "Space) Exit"; printf '%s' "Choice: "; key="$(ui_read_choice || true)"; printf '

'; status=0; case "${key:-}" in 1) run_migration_diff || status=$?;; 2) run_migration_migrate || status=$?;; 3) run_migration_rollback || status=$?;; 4) run_migration_status || status=$?;; 5) run_migration_execute || status=$?;; 6) show_file "$(runtime_log_file)" || status=$?; continue;; ''|' '|0|q|Q) exit 0;; *) ui_warn "Unknown action."; ui_pause_any; continue;; esac; ui_complete_action "$status"; done; }
main "$@"
