#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
export COMMANDING_DIR
source "$COMMANDING_DIR/lib/ui.sh"
TARGET_ROOT="$(resolve_target_root)"
CONSOLE_BIN="$(target_console_bin)"
ensure_cache_runtime() { require_command php || return 1; require_file "$CONSOLE_BIN" || return 1; }
smoke_check() { local as_json=0; [ "${1:-}" = "--json" ] && as_json=1; local detail="cache runtime ready"; if ! ensure_cache_runtime; then detail="cache runtime not available"; if [ $as_json -eq 1 ]; then emit_json_result fail cache.sh "$detail"; else ui_error "$detail"; fi; return 1; fi; if [ $as_json -eq 1 ]; then emit_json_result ok cache.sh "$detail"; else ui_note "$detail"; fi; }
run_cache_clear() { ensure_cache_runtime || return 1; run_logged_in_target "cache clear" php "$CONSOLE_BIN" cache:clear; }
run_cache_warmup() { ensure_cache_runtime || return 1; run_logged_in_target "cache warmup" php "$CONSOLE_BIN" cache:warmup; }
run_cache_clear_and_warmup() { ensure_cache_runtime || return 1; run_logged_in_target "cache clear" php "$CONSOLE_BIN" cache:clear; run_logged_in_target "cache warmup" php "$CONSOLE_BIN" cache:warmup; }
main() { case "${1:-}" in --smoke) shift || true; smoke_check "$@"; exit $?;; esac; while true; do ui_clear; ui_banner "Cache"; printf '%s
' "1) Clear cache" "2) Warmup cache" "3) Clear and warmup" "4) Open action log" "" "Space) Exit"; printf '%s' "Choice: "; key="$(ui_read_choice || true)"; printf '

'; status=0; case "${key:-}" in 1) run_cache_clear || status=$?;; 2) run_cache_warmup || status=$?;; 3) run_cache_clear_and_warmup || status=$?;; 4) show_file "$(runtime_log_file)" || status=$?; continue;; ''|' '|0|q|Q) exit 0;; *) ui_warn "Unknown action."; ui_pause_any; continue;; esac; ui_complete_action "$status"; done; }
main "$@"
