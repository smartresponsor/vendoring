#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"
while true; do
  ui_clear; ui_banner "Application"
  printf '%s
' '1) Routes' '2) Server' '3) Fixtures' '4) Schema' '5) Cache' '6) Migrations' '7) Logs' '' 'Space) Back'
  printf '%s' 'Choice: '
  action="$(ui_read_choice || true)"; printf '

'
  case "${action:-}" in
    ''|' ') exit 0 ;;
    1) bash "$COMMANDING_DIR/sh/route.sh" ;;
    2) bash "$COMMANDING_DIR/sh/server.sh" ;;
    3) bash "$COMMANDING_DIR/sh/fixture.sh" ;;
    4) bash "$COMMANDING_DIR/sh/schema.sh" ;;
    5) bash "$COMMANDING_DIR/sh/cache.sh" ;;
    6) bash "$COMMANDING_DIR/sh/migration.sh" ;;
    7) bash "$COMMANDING_DIR/sh/log.sh" ;;
    *) ui_warn "Unknown action: $action"; ui_pause_any ;;
  esac
 done
