#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
export COMMANDING_DIR
source "$COMMANDING_DIR/lib/ui.sh"
TARGET_ROOT="$(resolve_target_root)"
CONSOLE_BIN="$(target_console_bin)"
run_schema_validate() { require_command php || return 1; require_file "$CONSOLE_BIN" || return 1; run_logged_in_target "schema validate" php "$CONSOLE_BIN" doctrine:schema:validate; }
run_schema_update() { require_command php || return 1; require_file "$CONSOLE_BIN" || return 1; if ! ui_confirm "Run doctrine:schema:update --complete --force? [y/N]: "; then ui_note "Schema update cancelled."; return 0; fi; run_logged_in_target "schema update" php "$CONSOLE_BIN" doctrine:schema:update --complete --force; run_logged_in_target "schema validate after update" php "$CONSOLE_BIN" doctrine:schema:validate; }
main() { while true; do ui_clear; ui_banner "Schema"; printf '%s
' "1) Validate schema" "2) Update schema (force)" "3) Open action log" "" "Space) Exit"; printf '%s' "Choice: "; key="$(ui_read_choice || true)"; printf '

'; status=0; case "${key:-}" in 1) run_schema_validate || status=$?;; 2) run_schema_update || status=$?;; 3) show_file "$(runtime_log_file)" || status=$?; continue;; ''|' '|0|q|Q) exit 0;; *) ui_warn "Unknown action."; ui_pause_any; continue;; esac; ui_complete_action "$status"; done; }
main "$@"
