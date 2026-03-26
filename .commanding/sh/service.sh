#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
export COMMANDING_DIR
source "$COMMANDING_DIR/lib/ui.sh"
TARGET_ROOT="$(resolve_target_root)"
CONSOLE_BIN="$(target_console_bin)"
ensure_service_runtime() { require_command php || return 1; require_file "$CONSOLE_BIN" || return 1; }
service_list() { ensure_service_runtime || return 1; run_logged_in_target "service list" php "$CONSOLE_BIN" debug:container; }
service_find_by_partial() { ensure_service_runtime || return 1; local pattern=""; read -r -p "Service partial id: " pattern || true; if [ -z "$pattern" ]; then ui_warn "Service partial id is required."; return 1; fi; run_in_target bash -lc 'php "$1" debug:container | grep -i -- "$2"' _ "$CONSOLE_BIN" "$pattern"; }
service_show_one() { ensure_service_runtime || return 1; local service_id=""; read -r -p "Service id: " service_id || true; if [ -z "$service_id" ]; then ui_warn "Service id is required."; return 1; fi; run_logged_in_target "service show $service_id" php "$CONSOLE_BIN" debug:container "$service_id" --show-arguments; }
service_list_by_tag() { ensure_service_runtime || return 1; local tag_name=""; read -r -p "Tag name: " tag_name || true; if [ -z "$tag_name" ]; then ui_warn "Tag name is required."; return 1; fi; run_logged_in_target "service tag $tag_name" php "$CONSOLE_BIN" debug:container --tag="$tag_name"; }
while true; do ui_clear; ui_banner "Service"; printf '%s
' "Service Menu" "------------" "1) List all services" "2) Find service by partial id" "3) Show one service by id" "4) List services by tag" "5) Open action log" "" "Space) Exit"; printf '%s' "Choice: "; action="$(ui_pick_key)"; printf '
'; exit_code=0; case "${action:-}" in 1) service_list || exit_code=$?;; 2) service_find_by_partial || exit_code=$?;; 3) service_show_one || exit_code=$?;; 4) service_list_by_tag || exit_code=$?;; 5) show_file "$(runtime_log_file)" || exit_code=$?;; ""|0|q|Q) exit 0;; *) ui_warn "Unknown action.";; esac; ui_note "Exit code: $exit_code"; ui_pause_any; done
