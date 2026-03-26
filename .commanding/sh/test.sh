#!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
# Source-of-truth: root script. Embedded dot copies are projections.
set -euo pipefail

COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
export COMMANDING_DIR

source "$COMMANDING_DIR/lib/ui.sh"

PROJECT_ROOT="$(detect_project_root)"
TARGET_ROOT=""
PHPUNIT_BIN=""
PHPUNIT_CONFIG=""

resolve_test_target_root() {
  local commanding_root="$COMMANDING_DIR"
  local commanding_name="$(basename -- "$commanding_root")"

  if [ "$commanding_name" = ".commanding" ]; then
    cd -- "$commanding_root/.." && pwd
    return 0
  fi

  if [ -d "$commanding_root/.sandbox" ]; then
    cd -- "$commanding_root/.sandbox" && pwd
    return 0
  fi

  printf '%s
' "$PROJECT_ROOT"
}

resolve_phpunit_bin() {
  local root="$1"
  if [ -x "$root/vendor/bin/phpunit" ] || [ -f "$root/vendor/bin/phpunit" ]; then
    printf '%s
' "$root/vendor/bin/phpunit"
    return 0
  fi
  return 1
}

resolve_phpunit_config() {
  local root="$1"
  if [ -f "$root/phpunit.xml" ]; then printf '%s
' "$root/phpunit.xml"; return 0; fi
  if [ -f "$root/phpunit.xml.dist" ]; then printf '%s
' "$root/phpunit.xml.dist"; return 0; fi
  return 1
}

phpunit_config_declares_suite() {
  local suite_name="$1"
  local config_path="$2"
  [ -n "$config_path" ] || return 1
  [ -f "$config_path" ] || return 1
  grep -Eq "<testsuite[^>]*name=["']${suite_name}["']" "$config_path"
}

find_conventional_suite_dir() {
  local root="$1"
  local suite_name="$2"
  local candidate=""
  case "$suite_name" in
    unit)
      for candidate in "$root/tests/Unit" "$root/tests/unit" "$root/tests"; do
        [ -d "$candidate" ] && { printf '%s
' "$candidate"; return 0; }
      done ;;
    integration)
      for candidate in "$root/tests/Integration" "$root/tests/integration"; do
        [ -d "$candidate" ] && { printf '%s
' "$candidate"; return 0; }
      done ;;
    e2e)
      for candidate in "$root/tests/E2E" "$root/tests/E2e" "$root/tests/e2e" "$root/tests/EndToEnd"; do
        [ -d "$candidate" ] && { printf '%s
' "$candidate"; return 0; }
      done ;;
  esac
  return 1
}

require_test_runtime() {
  TARGET_ROOT="$(resolve_test_target_root)"
  command_exists php || { ui_error "PHP is not available in PATH"; return 1; }
  if ! PHPUNIT_BIN="$(resolve_phpunit_bin "$TARGET_ROOT")"; then
    ui_error "vendor/bin/phpunit not found in target root: $TARGET_ROOT"
    ui_note "Hint: run composer install in the target root."
    return 1
  fi
  PHPUNIT_CONFIG="$(resolve_phpunit_config "$TARGET_ROOT" || true)"
  return 0
}

smoke_check() {
  local as_json=0
  [ "${1:-}" = "--json" ] && as_json=1
  local detail="test runtime ready"
  if ! require_test_runtime; then
    detail="phpunit runtime not available"
    if [ $as_json -eq 1 ]; then emit_json_result fail test.sh "$detail"; else ui_error "$detail"; fi
    return 1
  fi
  detail="test runtime ready (target=$TARGET_ROOT)"
  if [ $as_json -eq 1 ]; then emit_json_result ok test.sh "$detail"; else ui_note "$detail"; fi
}

run_in_target_action() {
  local label="$1"
  shift || true
  ensure_runtime_dirs
  log_action "$label [target:$TARGET_ROOT]"
  if ( cd -- "$TARGET_ROOT"; "$@" ) 2>>"$(runtime_error_file)"; then
    log_action "$label [ok]"
    return 0
  fi
  local exit_code=$?
  log_action "$label [exit:$exit_code]"
  return "$exit_code"
}

run_phpunit_full() {
  local args=("$PHPUNIT_BIN")
  if [ -n "$PHPUNIT_CONFIG" ]; then
    args+=("-c" "$PHPUNIT_CONFIG")
    ui_note "PHPUnit config: $PHPUNIT_CONFIG"
  else
    ui_warn "phpunit.xml(.dist) not found in target root; running PHPUnit without explicit config"
  fi
  ui_note "Test runtime root: $TARGET_ROOT"
  run_in_target_action "PHPUnit full suite" "${args[@]}"
}

run_phpunit_suite() {
  local suite_name="$1"
  local conventional_dir=""
  local args=("$PHPUNIT_BIN")
  ui_note "Test runtime root: $TARGET_ROOT"
  if [ -n "$PHPUNIT_CONFIG" ]; then
    ui_note "PHPUnit config: $PHPUNIT_CONFIG"
    if phpunit_config_declares_suite "$suite_name" "$PHPUNIT_CONFIG"; then
      args+=("-c" "$PHPUNIT_CONFIG" "--testsuite=$suite_name")
      run_in_target_action "PHPUnit ${suite_name} suite" "${args[@]}"
      return 0
    fi
  fi
  conventional_dir="$(find_conventional_suite_dir "$TARGET_ROOT" "$suite_name" || true)"
  if [ -n "$conventional_dir" ]; then
    [ -n "$PHPUNIT_CONFIG" ] && args+=("-c" "$PHPUNIT_CONFIG")
    args+=("$conventional_dir")
    ui_warn "Suite '$suite_name' is not declared; falling back to directory: $conventional_dir"
    run_in_target_action "PHPUnit ${suite_name} directory" "${args[@]}"
    return 0
  fi
  ui_warn "Requested suite '$suite_name' is not declared and no conventional directory was found in target root."
  ui_warn "Target root: $TARGET_ROOT"
  return 0
}

unit_test() { ui_note "Running unit test suite..."; run_phpunit_suite unit; }
integration_test() { ui_note "Running integration test suite..."; run_phpunit_suite integration; }
e2e_test() { ui_note "Running e2e test suite..."; run_phpunit_suite e2e; }
full_test() { ui_note "Running full PHPUnit suite from target root..."; run_phpunit_full; }
open_test_log() { show_file "$(action_log_file)"; }
print_menu() { ui_clear; ui_banner "Test"; printf '%s
' "Tests Menu" "----------" "1) Unit tests" "2) Integration tests" "3) E2E tests" "4) Full suite" "5) Open action log" "Space) Exit"; }
main() {
  case "${1:-}" in
    --smoke) shift || true; smoke_check "$@"; exit $? ;;
    --direct)
      shift || true
      if ! require_test_runtime; then
        exit 1
      fi
      local direct_action="${1:-full}"
      case "$direct_action" in
        full) full_test ; exit $? ;;
        unit) unit_test ; exit $? ;;
        integration) integration_test ; exit $? ;;
        e2e) e2e_test ; exit $? ;;
        log) open_test_log ; exit $? ;;
        smokecheck) smoke_check ; exit $? ;;
        *) ui_error "Unknown direct test action: $direct_action"; exit 1 ;;
      esac
      ;;
  esac
  if ! require_test_runtime; then ui_pause_any; exit 0; fi
  while true; do
    ui_note "Resolved target root: $TARGET_ROOT"
    print_menu
    printf '%s' 'Choice: '
    action="$(ui_read_choice || true)"
    printf '

'
    status=0
    case "${action:-}" in
      1) unit_test || status=$? ;;
      2) integration_test || status=$? ;;
      3) e2e_test || status=$? ;;
      4) full_test || status=$? ;;
      5) open_test_log || status=$? ; continue ;;
      ''|' '|0|q|Q) exit 0 ;;
      *) ui_warn "Unknown action: ${action:-}"; ui_pause_any; continue ;;
    esac
    ui_complete_action "$status"
  done
}
main "$@"
