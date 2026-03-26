#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"
while true; do
  ui_clear; ui_banner "Diagnostics"
  printf '%s
' '1) Inspection' '2) Logs' '3) Routes' '4) GraphQL schema dump' '5) Metrics dump' '6) Health' '' 'Space) Back'
  printf '%s' 'Choice: '
  action="$(ui_read_choice || true)"; printf '

'
  case "${action:-}" in
    ''|' ') exit 0 ;;
    1) bash "$COMMANDING_DIR/sh/inspection.sh" ;;
    2) bash "$COMMANDING_DIR/sh/log.sh" ;;
    3) bash "$COMMANDING_DIR/sh/route.sh" ;;
    4) bash "$COMMANDING_DIR/sh/report-category-graphql-schema-dump.sh" ;;
    5) bash "$COMMANDING_DIR/sh/report-category-metrics-dump.sh" ;;
    6) bash "$COMMANDING_DIR/sh/health.sh" ;;
    *) ui_warn "Unknown action: $action"; ui_pause_any ;;
  esac
 done
