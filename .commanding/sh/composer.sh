#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
export COMMANDING_DIR
source "$COMMANDING_DIR/lib/ui.sh"
TARGET_ROOT="$(resolve_target_root)"
COMPOSER_JSON="$(target_composer_json)"
require_command composer || exit 1
require_command php || exit 1
require_file "$COMPOSER_JSON" || exit 1
has_composer_script() { local script_name="${1:-}"; php -r '$file = $argv[1]; $script = $argv[2]; if (!is_file($file)) { exit(1); } $data = json_decode(file_get_contents($file), true); if (!is_array($data)) { exit(2); } if (!isset($data["scripts"]) || !is_array($data["scripts"])) { exit(3); } exit(array_key_exists($script, $data["scripts"]) ? 0 : 4);' "$COMPOSER_JSON" "$script_name" >/dev/null 2>&1; }
run_composer() { local label="$1"; shift; run_logged_in_target "$label" composer "$@"; }
run_named_script() { local script_name="$1"; local label="$2"; if has_composer_script "$script_name"; then run_composer "$label" "$script_name"; return $?; fi; ui_warn "composer script not found: $script_name"; return 1; }

main() {
  while true; do
    ui_clear
    ui_banner "Composer"
    printf '%s
' "Composer Menu" "-------------" "1) Install" "2) Update" "3) Dump autoload" "4) QA script" "5) Inspection run script" "6) Inspection latest script" "7) Validate composer.json" "8) Open action log" "" "Space) Exit"
    printf '%s' "Choice: "
    action="$(ui_read_choice || true)"
    printf '

'

    exit_code=0
    case "${action:-}" in
      ''|' '|0|q|Q) exit 0 ;;
      1) run_composer "Composer install" install || exit_code=$? ;;
      2) run_composer "Composer update" update || exit_code=$? ;;
      3) run_composer "Composer dump-autoload" dump-autoload || exit_code=$? ;;
      4) run_named_script "qa" "Composer qa" || exit_code=$? ;;
      5)
        if has_composer_script "inspection:run"; then
          run_named_script "inspection:run" "Composer inspection:run" || exit_code=$?
        elif has_composer_script "inspection"; then
          run_named_script "inspection" "Composer inspection" || exit_code=$?
        else
          ui_warn "inspection composer scripts are not available."
          ui_pause_any
          continue
        fi
        ;;
      6) run_named_script "inspection:latest" "Composer inspection:latest" || exit_code=$? ;;
      7) run_composer "Composer validate" validate || exit_code=$? ;;
      8) show_file "$(runtime_log_file)" || exit_code=$? ; continue ;;
      *) ui_warn "Unknown action."; ui_pause_any; continue ;;
    esac

    ui_complete_action "$exit_code"
  done
}

main "$@"
