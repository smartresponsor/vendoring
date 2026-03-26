#!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
# Source-of-truth: root script. Embedded dot copies are projections.

[ -f /etc/profile ] && . /etc/profile
[ -f ~/.bashrc ] && . ~/.bashrc
set -euo pipefail

COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"}"
export COMMANDING_DIR

# shellcheck source=/dev/null
source "$COMMANDING_DIR/lib/ui.sh"

COMMANDING_SH_DIR="$COMMANDING_DIR/sh"

fail() {
  local message="${1:-Error}"
  ui_error "$message"
  ui_pause_any
  return 0
}

resolve_script() {
  local action="${1:-}"

  case "$action" in
    1) printf '%s' "$COMMANDING_SH_DIR/route.sh" ;;
    2) printf '%s' "$COMMANDING_SH_DIR/server.sh" ;;
    3) printf '%s' "$COMMANDING_SH_DIR/fixture.sh" ;;
    4) printf '%s' "$COMMANDING_SH_DIR/schema.sh" ;;
    5) printf '%s' "$COMMANDING_SH_DIR/patch-zipper.sh" ;;
    6) printf '%s' "$COMMANDING_SH_DIR/test.sh" ;;
    7) printf '%s' "$COMMANDING_SH_DIR/docker.sh" ;;
    8) printf '%s' "$COMMANDING_SH_DIR/migration.sh" ;;
    9) printf '%s' "$COMMANDING_SH_DIR/composer.sh" ;;
    i|I) printf '%s' "$COMMANDING_SH_DIR/inspection.sh" ;;
    g|G) printf '%s' "$COMMANDING_DIR/git/commanding.sh" ;;
    l|L) printf '%s' "$COMMANDING_SH_DIR/log.sh" ;;
    c|C) printf '%s' "$COMMANDING_SH_DIR/cache.sh" ;;
    d) printf '%s' "$COMMANDING_SH_DIR/dot.sh" ;;
    h|H) printf '%s' "$COMMANDING_SH_DIR/health.sh" ;;
    u|U) printf '%s' "$COMMANDING_SH_DIR/send-ui.sh" ;;
    s|S) printf '%s' "$COMMANDING_SH_DIR/send-status.sh" ;;
    A) printf '%s' "$COMMANDING_SH_DIR/menu-application.sh" ;;
    D) printf '%s' "$COMMANDING_SH_DIR/menu-data.sh" ;;
    Q) printf '%s' "$COMMANDING_SH_DIR/menu-quality.sh" ;;
    R) printf '%s' "$COMMANDING_SH_DIR/menu-runtime.sh" ;;
    P) printf '%s' "$COMMANDING_SH_DIR/menu-repository.sh" ;;
    F) printf '%s' "$COMMANDING_SH_DIR/menu-infrastructure.sh" ;;
    I) printf '%s' "$COMMANDING_SH_DIR/menu-diagnostics.sh" ;;
    *) return 1 ;;
  esac
}

ensure_target() {
  local target="${1:-}"
  [ -n "$target" ] || return 1
  [ -f "$target" ] || return 1
  return 0
}

is_long_running() {
  case "${1:-}" in
    2) return 0 ;;
    *) return 1 ;;
  esac
}

is_interactive_terminal() {
  [ -t 0 ] && [ -t 1 ]
}

handle_command_result() {
  local status="${1:-0}"
  if [ "$status" -eq 0 ]; then
    return 0
  fi

  if is_interactive_terminal; then
    ui_action_failure "$status"
    return 0
  fi

  return "$status"
}

self_check() {
  local as_json=0
  [ "${1:-}" = "--json" ] && as_json=1

  local key target missing=()
  for key in 1 2 3 4 5 6 7 8 9 i g l c d h u s A D Q R P F I; do
    target="$(resolve_script "$key" || true)"
    if ! ensure_target "$target"; then
      missing+=("$key")
    fi
  done

  if [ ${#missing[@]} -eq 0 ]; then
    if [ $as_json -eq 1 ]; then
      emit_json_result ok run.sh "dispatch map ok"
    else
      ui_note "run.sh self-check passed."
    fi
    return 0
  fi

  local detail="unresolved dispatch targets: ${missing[*]}"
  if [ $as_json -eq 1 ]; then
    emit_json_result fail run.sh "$detail"
  else
    ui_error "$detail"
  fi
  return 1
}

run_short() {
  local target="$1"
  shift || true

  set +e
  bash "$target" "$@"
  local status=$?
  set -e

  if [ $status -ne 0 ]; then
    ui_action_failure "$status"
    return 0
  fi

  ui_action_success
  return 0
}

run_long() {
  local target="$1"
  shift || true

  set +e
  bash "$target" "$@"
  local status=$?
  set -e

  if [ $status -ne 0 ]; then
    ui_action_failure "$status"
    return 0
  fi

  ui_action_success
  return 0
}

single() {
  local action="${1:-}"
  shift || true
  local target

  if ! target="$(resolve_script "$action")"; then
    fail "Unknown input: $action"
    return 0
  fi

  if ! ensure_target "$target"; then
    fail "Script not found: $target"
    return 0
  fi

  log_action "run single: $action -> $target"

  if is_long_running "$action"; then
    run_long "$target" "$@"
  else
    run_short "$target" "$@"
  fi

  return 0
}

chain() {
  local digits="${1:-}"
  [ -n "$digits" ] || fail 'Empty chain'

  local i ch target status
  local len="${#digits}"

  log_action "run chain: $digits"

  for (( i=0; i<len; i++ )); do
    ch="${digits:i:1}"

    if [[ "$ch" == '0' ]]; then
      return 0
    fi

    if [[ ! "$ch" =~ ^[0-9]$ ]]; then
      fail "Invalid chain action: $ch"
      return 0
    fi

    if ! target="$(resolve_script "$ch")"; then
      fail "Unknown chain step: $ch"
      return 0
    fi

    if ! ensure_target "$target"; then
      fail "Script not found: $target"
      return 0
    fi

    set +e
    bash "$target"
    status=$?
    set -e

    if [ $status -ne 0 ]; then
      ui_action_failure "$status"
      return 0
    fi
  done

  ui_action_success
  return 0
}


list_actions() {
  cat <<'EOF'
Available commanding actions
---------------------------
Native actions:
  test
  test:unit
  test:integration
  test:e2e
  qa:full
  qa:style
  qa:static
  qa:smell
  cs:check
  cs:fix
  stan
  md
  md:tests
  health:sandbox
  health:application
  support:bundle
  agent:prepare
  send:ui
  send:last
  send:status
  send:doctor
  send:retry
  send:open-payload
  send:open-manifest
  send:open-request
  send:open-log
  send:open-result
  send:open-bundle
  chat:bind <url-or-id>
  chat:show
  chat:clear
  doctor

Composer proxy:
  composer <script>
  app <script>

Examples:
  ./commanding.sh test
  ./commanding.sh qa:full
  ./commanding.sh support:bundle
  ./commanding.sh composer test
  ./commanding.sh app qa:full
EOF
}

show_help() {
  list_actions
}

doctor() {
  if is_interactive_terminal; then
    ui_clear
  fi
  ui_banner "Commanding / Doctor"
  local target=""
  target="$(resolve_target_root)"
  printf '%s
' ' Checks'
  printf '%s
' ' ------'
  ui_status_line 'Target:' "$target"
  ui_status_line 'PHP:' "$(command_exists php && printf ok || printf missing)"
  ui_status_line 'Composer:' "$(command_exists composer && printf ok || printf missing)"
  ui_status_line 'Console:' "$(target_has_console && printf ok || printf missing)"
  ui_status_line 'PHPUnit:' "$(target_has_phpunit && printf ok || printf missing)"
  ui_status_line 'composer.json:' "$(target_has_composer && printf ok || printf missing)"
  return 0
}

run_target_composer_script() {
  local script_name="${1:-}"
  [ -n "$script_name" ] || { ui_error 'Composer script name is required.'; return 1; }
  require_command composer || return 1
  require_file "$(target_composer_json)" || return 1
  if ! target_has_composer_script "$script_name"; then
    ui_error "composer script not found in target root: $script_name"
    return 1
  fi
  run_logged_in_target "composer $script_name" composer "$script_name"
}

run_native_action() {
  local action="${1:-}"
  case "$action" in
    test) bash "$COMMANDING_SH_DIR/test.sh" --direct full ; return $? ;;
    test:unit) bash "$COMMANDING_SH_DIR/test.sh" --direct unit ; return $? ;;
    test:integration) bash "$COMMANDING_SH_DIR/test.sh" --direct integration ; return $? ;;
    test:e2e) bash "$COMMANDING_SH_DIR/test.sh" --direct e2e ; return $? ;;
    health:sandbox) bash "$COMMANDING_SH_DIR/health-sandbox-sweep.sh" sandbox ; return $? ;;
    health:application) bash "$COMMANDING_SH_DIR/health-sandbox-sweep.sh" application ; return $? ;;
    support:bundle) bash "$COMMANDING_SH_DIR/support-bundle.sh" "$@" ; return $? ;;
    agent:prepare) bash "$COMMANDING_SH_DIR/agent-prepare.sh" "$@" ; return $? ;;
    send:ui|send:last|send:retry) bash "$COMMANDING_SH_DIR/send-ui.sh" "$@" ; return $? ;;
    send:status) bash "$COMMANDING_SH_DIR/send-status.sh" "$@" ; return $? ;;
    send:doctor) bash "$COMMANDING_SH_DIR/send-doctor.sh" "$@" ; return $? ;;
    send:open-payload) bash "$COMMANDING_SH_DIR/send-open.sh" payload ; return $? ;;
    send:open-manifest) bash "$COMMANDING_SH_DIR/send-open.sh" manifest ; return $? ;;
    send:open-request) bash "$COMMANDING_SH_DIR/send-open.sh" request ; return $? ;;
    send:open-log) bash "$COMMANDING_SH_DIR/send-open.sh" log ; return $? ;;
    send:open-result) bash "$COMMANDING_SH_DIR/send-open.sh" result ; return $? ;;
    send:open-bundle) bash "$COMMANDING_SH_DIR/send-open.sh" bundle ; return $? ;;
    chat:bind) bash "$COMMANDING_SH_DIR/chat-bind.sh" "$@" ; return $? ;;
    chat:show) bash "$COMMANDING_SH_DIR/chat-show.sh" "$@" ; return $? ;;
    chat:clear) bash "$COMMANDING_SH_DIR/chat-clear.sh" "$@" ; return $? ;;
    doctor) doctor ; return $? ;;
    qa:full|qa:style|qa:static|qa:smell|cs:check|cs:fix|stan|md|md:tests)
      if target_has_composer_script "$action"; then
        run_target_composer_script "$action"
        return $?
      fi
      ui_error "No composer script and no direct fallback registered for action: $action"
      return 1
      ;;
    *)
      return 1
      ;;
  esac
}

command_mode() {
  local action="${1:-}"
  shift || true

  case "$action" in
    ''|help|--help|-h) show_help; return 0 ;;
    list) list_actions; return 0 ;;
    doctor) doctor; return 0 ;;
    support:bundle) bash "$COMMANDING_SH_DIR/support-bundle.sh" "$@"; return $? ;;
    agent:prepare) bash "$COMMANDING_SH_DIR/agent-prepare.sh" "$@"; return $? ;;
    send:ui|send:last|send:retry) bash "$COMMANDING_SH_DIR/send-ui.sh" "$@"; return $? ;;
    send:status) bash "$COMMANDING_SH_DIR/send-status.sh" "$@"; return $? ;;
    send:doctor) bash "$COMMANDING_SH_DIR/send-doctor.sh" "$@"; return $? ;;
    chat:bind) bash "$COMMANDING_SH_DIR/chat-bind.sh" "$@"; return $? ;;
    chat:show) bash "$COMMANDING_SH_DIR/chat-show.sh" "$@"; return $? ;;
    chat:clear) bash "$COMMANDING_SH_DIR/chat-clear.sh" "$@"; return $? ;;
    composer|app)
      local script_name="${1:-}"
      [ -n "$script_name" ] || { ui_error 'Usage: ./commanding.sh composer <script>'; return 1; }
      run_target_composer_script "$script_name"
      return $?
      ;;
  esac

  if run_native_action "$action"; then
    return 0
  fi

  if target_has_composer_script "$action"; then
    run_target_composer_script "$action"
    return $?
  fi

  ui_error "Unknown commanding action: $action"
  return 1
}

main() {
  local cmd="${1:-}"
  shift || true

  case "$cmd" in
    --self-check)
      self_check "$@"
      return $?
      ;;
    '')
      fail 'No command provided to run.sh'
      return 0
      ;;
    chain) chain "${1:-}" ;;
    list|help|--help|-h|doctor|composer|app|test|test:unit|test:integration|test:e2e|qa:full|qa:style|qa:static|qa:smell|cs:check|cs:fix|stan|md|md:tests|health:sandbox|health:application|support:bundle|agent:prepare|send:ui|send:last|send:status|send:doctor|send:retry|send:open-payload|send:open-manifest|send:open-request|send:open-log|send:open-result|send:open-bundle|chat:bind|chat:show|chat:clear)
      command_mode "$cmd" "$@"; handle_command_result $?
      ;;
    *)
      if resolve_script "$cmd" >/dev/null 2>&1; then
        single "$cmd" "$@"
      else
        command_mode "$cmd" "$@"; handle_command_result $?
      fi
      ;;
  esac
}

main "$@"
