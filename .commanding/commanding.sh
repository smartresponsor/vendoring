#!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
# Source-of-truth: root script. Embedded dot copies are projections.
set -euo pipefail

COMMANDING_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
export COMMANDING_DIR

# shellcheck source=/dev/null
source "$COMMANDING_DIR/lib/ui.sh"
if [ -f "$COMMANDING_DIR/sh/health-lib.sh" ]; then
  # shellcheck source=/dev/null
  source "$COMMANDING_DIR/sh/health-lib.sh"
fi

self_check() {
  local as_json=0
  [ "${1:-}" = "--json" ] && as_json=1

  local missing=()
  local required=(
    "$COMMANDING_DIR/commanding.sh"
    "$COMMANDING_DIR/run.sh"
    "$COMMANDING_DIR/lib/ui.sh"
    "$COMMANDING_DIR/sh/log.sh"
    "$COMMANDING_DIR/sh/inspection.sh"
    "$COMMANDING_DIR/sh/cache.sh"
    "$COMMANDING_DIR/sh/test.sh"
    "$COMMANDING_DIR/sh/menu-application.sh"
    "$COMMANDING_DIR/sh/menu-data.sh"
    "$COMMANDING_DIR/sh/menu-quality.sh"
    "$COMMANDING_DIR/sh/menu-runtime.sh"
    "$COMMANDING_DIR/sh/menu-repository.sh"
    "$COMMANDING_DIR/sh/menu-diagnostics.sh"
    "$COMMANDING_DIR/sh/menu-infrastructure.sh"
    "$COMMANDING_DIR/sh/health.sh"
    "$COMMANDING_DIR/sh/health-sandbox-sweep.sh"
    "$COMMANDING_DIR/sh/health-rerun-failed.sh"
    "$COMMANDING_DIR/sh/health-open-failed-log.sh"
    "$COMMANDING_DIR/sh/health-list-probes.sh"
    "$COMMANDING_DIR/sh/health-run-selected.sh"
    "$COMMANDING_DIR/sh/support-bundle.sh"
    "$COMMANDING_DIR/sh/patch-zipper.sh"
    "$COMMANDING_DIR/sh/chat-bind.sh"
    "$COMMANDING_DIR/sh/chat-show.sh"
    "$COMMANDING_DIR/sh/chat-clear.sh"
    "$COMMANDING_DIR/sh/agent-prepare.sh"
    "$COMMANDING_DIR/sh/send-ui.sh"
    "$COMMANDING_DIR/sh/send-status.sh"
    "$COMMANDING_DIR/sh/send-doctor.sh"
    "$COMMANDING_DIR/sh/send-open.sh"
    "$COMMANDING_DIR/sh/send-ui-hook-example.sh"
  )

  local path=''
  for path in "${required[@]}"; do
    [ -f "$path" ] || missing+=("${path#$COMMANDING_DIR/}")
  done

  if [ ${#missing[@]} -eq 0 ]; then
    if [ $as_json -eq 1 ]; then
      emit_json_result ok commanding "self-check passed"
    else
      ui_note "Commanding self-check passed."
    fi
    return 0
  fi

  local detail="missing: ${missing[*]}"
  if [ $as_json -eq 1 ]; then
    emit_json_result fail commanding "$detail"
  else
    ui_error "$detail"
  fi
  return 1
}

print_quick_menu() {
  ui_clear
  ui_banner "Commanding / Quick"
  printf '%s\n' ' 1) Routes       6) Tests         d) Dot'
  printf '%s\n' ' 2) Server       7) Docker        g) Git'
  printf '%s\n' ' 3) Fixtures     8) Migrations    c) Cache'
  printf '%s\n' ' 4) Schema       9) Composer      l) Logs'
  printf '%s\n' ' 5) Patch bundle i) Inspection    s) Send status'
  printf '%s\n' ' h) Health        u) Send UI'
  if declare -F health_latest_status_line >/dev/null 2>&1; then
    printf '\n'
    printf ' %s\n' "$(health_latest_status_line)"
  fi
  if chat_binding_exists; then
    printf ' %s\n' "Chat: $(chat_binding_id 2>/dev/null || printf bound)"
  else
    printf ' %s\n' 'Chat: not bound'
  fi
  printf '\n'
  printf '%s\n' ' 0) Exit'
  printf '%s\n' ' r) Refresh'
  printf '%s\n' ' v) Structured dashboard'
  printf '%s\n' ' Empty/space = exit'
}

print_structured_menu() {
  ui_clear
  ui_banner "Commanding / Structured"
  printf '%s\n' ' 1) Application      5) Repository'
  printf '%s\n' ' 2) Data             6) Diagnostics'
  printf '%s\n' ' 3) Quality          7) Infrastructure'
  printf '%s\n' ' 4) Runtime'
  printf '\n'
  printf '%s\n' ' 0) Exit'
  printf '%s\n' ' r) Refresh'
  printf '%s\n' ' v) Quick dashboard'
  printf '%s\n' ' Empty/space = exit'
}

dispatch_quick() {
  local line="${1:-}"
  if [[ "$line" =~ ^[[:space:]]*$ ]]; then
    return 1
  fi

  case "$line" in
    0) return 1 ;;
    r|R) return 0 ;;
    v|V|$'\t') DASHBOARD_MODE='structured'; return 0 ;;
  esac

  if [[ "$line" =~ ^[0-9]+$ ]]; then
    bash "$COMMANDING_DIR/run.sh" chain "$line" || true
    return 0
  fi

  bash "$COMMANDING_DIR/run.sh" "$line" || true
  return 0
}

dispatch_structured() {
  local line="${1:-}"
  if [[ "$line" =~ ^[[:space:]]*$ ]]; then
    return 1
  fi

  case "$line" in
    0) return 1 ;;
    r|R) return 0 ;;
    v|V|$'\t') DASHBOARD_MODE='quick'; return 0 ;;
    1) bash "$COMMANDING_DIR/run.sh" A || true ;;
    2) bash "$COMMANDING_DIR/run.sh" D || true ;;
    3) bash "$COMMANDING_DIR/run.sh" Q || true ;;
    4) bash "$COMMANDING_DIR/run.sh" R || true ;;
    5) bash "$COMMANDING_DIR/run.sh" P || true ;;
    6) bash "$COMMANDING_DIR/run.sh" I || true ;;
    7) bash "$COMMANDING_DIR/run.sh" F || true ;;
    *) ui_warn "Unknown input: $line" ;;
  esac
  return 0
}

menu_loop() {
  while true; do
    if [ "$DASHBOARD_MODE" = 'structured' ]; then
      print_structured_menu
    else
      print_quick_menu
    fi
    printf '%s' ' Select: '

    local line=''
    line="$(ui_read_choice || true)"
    printf '\n\n'

    if [ "$DASHBOARD_MODE" = 'structured' ]; then
      dispatch_structured "$line" || break
    else
      dispatch_quick "$line" || break
    fi
  done
}

main() {
  DASHBOARD_MODE="${COMMANDING_DASHBOARD_MODE:-quick}"
  export DASHBOARD_MODE

  case "${1:-}" in
    --self-check)
      shift || true
      self_check "$@"
      return $?
      ;;
  esac

  if [ $# -ge 1 ]; then
    case "$1" in
      list|help|--help|-h|doctor|composer|app|test|test:unit|test:integration|test:e2e|qa:full|qa:style|qa:static|qa:smell|cs:check|cs:fix|stan|md|md:tests|health:sandbox|health:application|support:bundle|agent:prepare|send:ui|send:last|send:status|send:doctor|send:retry|send:open-payload|send:open-manifest|send:open-request|send:open-log|send:open-result|send:open-bundle|chat:bind|chat:show|chat:clear)
        bash "$COMMANDING_DIR/run.sh" "$@"
        return $?
        ;;
    esac

    if [ "$DASHBOARD_MODE" = 'structured' ]; then
      dispatch_structured "$1" || true
    else
      dispatch_quick "$1" || true
    fi
    return 0
  fi

  if [ ! -t 0 ]; then
    printf '%s\n' 'Commanding requires an interactive terminal.'
    return 1
  fi

  menu_loop
}

main "$@"
