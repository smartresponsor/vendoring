#!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
# Source-of-truth: root script. Embedded dot copies are projections.
set -euo pipefail

COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
export COMMANDING_DIR
source "$COMMANDING_DIR/lib/ui.sh"

PROJECT_ROOT="$(detect_project_root)"

require_docker_runtime() {
  if ! command_exists docker; then
    ui_error "Docker is not available in PATH"
    return 1
  fi
  if [ ! -f "$PROJECT_ROOT/docker/compose.yml" ] && [ ! -d "$PROJECT_ROOT/docker/compose" ]; then
    ui_error "Docker compose files were not found under: $PROJECT_ROOT/docker"
    return 1
  fi
  return 0
}

run_docker_action() {
  local label="$1"
  shift || true
  run_logged "$label" "$@"
}

docker_up() { ui_note "Starting local container stack..."; run_docker_action "Docker up" docker compose up -d; }
docker_down() { ui_note "Stopping local container stack..."; run_docker_action "Docker down" docker compose down; }
docker_logs() {
  ui_note "Streaming Docker logs..."
  ( cd "$PROJECT_ROOT" && docker compose logs -f --tail 200 )
}

print_menu() {
  ui_clear
  ui_banner "Docker"
  printf '%s
' "Docker Menu" "-----------" "1) Up" "2) Down" "3) Logs" "4) Open Docker base README" "5) Open Docker base manifest" "Space) Exit"
}

main() {
  if ! require_docker_runtime; then
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
      1) docker_up || status=$? ;;
      2) docker_down || status=$? ;;
      3) docker_logs || status=$? ;;
      4) show_file "$COMMANDING_DIR/docker/README.md" || status=$? ; continue ;;
      5) show_file "$COMMANDING_DIR/docker/MANIFEST.md" || status=$? ; continue ;;
      ''|' '|0|q|Q) exit 0 ;;
      *) ui_warn "Unknown action: ${action:-}"; ui_pause_any; continue ;;
    esac
    ui_complete_action "$status"
  done
}

main "$@"
