#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"
while true; do
  ui_clear; ui_banner "Infrastructure"
  printf '%s
' '1) Docker' '2) Deploy' '3) Canary' '4) Harness' '5) Install' '6) Push ClickHouse metrics' '' 'Space) Back'
  printf '%s' 'Choice: '
  action="$(ui_read_choice || true)"; printf '

'
  case "${action:-}" in
    ''|' ') exit 0 ;;
    1) bash "$COMMANDING_DIR/sh/docker.sh" ;;
    2) bash "$COMMANDING_DIR/sh/deploy.sh" ;;
    3) bash "$COMMANDING_DIR/sh/canary-catalog-run.sh" ;;
    4) bash "$COMMANDING_DIR/sh/harness-category-run.sh" ;;
    5) bash "$COMMANDING_DIR/sh/install-category.sh" ;;
    6) bash "$COMMANDING_DIR/sh/metrics-category-push-clickhouse.sh" ;;
    *) ui_warn "Unknown action: $action"; ui_pause_any ;;
  esac
 done
