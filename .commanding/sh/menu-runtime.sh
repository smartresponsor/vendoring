#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"
while true; do
  ui_clear; ui_banner "Runtime"
  printf '%s
' '1) Server' '2) Docker' '3) Cache' '4) Service' '5) PHP 8.2 wrapper' '6) PHP 8.4 wrapper' '' 'Space) Back'
  printf '%s' 'Choice: '
  action="$(ui_read_choice || true)"; printf '

'
  case "${action:-}" in
    ''|' ') exit 0 ;;
    1) bash "$COMMANDING_DIR/sh/server.sh" ;;
    2) bash "$COMMANDING_DIR/sh/docker.sh" ;;
    3) bash "$COMMANDING_DIR/sh/cache.sh" ;;
    4) bash "$COMMANDING_DIR/sh/service.sh" ;;
    5) bash "$COMMANDING_DIR/sh/php-82.sh" ;;
    6) bash "$COMMANDING_DIR/sh/php-84.sh" ;;
    *) ui_warn "Unknown action: $action"; ui_pause_any ;;
  esac
 done
