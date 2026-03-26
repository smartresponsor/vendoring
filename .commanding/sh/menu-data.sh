#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"
while true; do
  ui_clear; ui_banner "Data"
  printf '%s
' '1) Backup create' '2) Backup restore' '3) Import run' '4) Export audit' '5) Export telemetry' '6) DX seed' '7) DX load fixtures' '' 'Space) Back'
  printf '%s' 'Choice: '
  action="$(ui_read_choice || true)"; printf '

'
  case "${action:-}" in
    ''|' ') exit 0 ;;
    1) bash "$COMMANDING_DIR/sh/backup-category-create.sh" ;;
    2) bash "$COMMANDING_DIR/sh/backup-category-restore.sh" ;;
    3) bash "$COMMANDING_DIR/sh/import-category-run.sh" ;;
    4) bash "$COMMANDING_DIR/sh/export-category-audit.sh" ;;
    5) bash "$COMMANDING_DIR/sh/export-category-telemetry.sh" ;;
    6) bash "$COMMANDING_DIR/sh/dx-category-seed.sh" ;;
    7) bash "$COMMANDING_DIR/sh/dx-category-load-fixtures.sh" ;;
    *) ui_warn "Unknown action: $action"; ui_pause_any ;;
  esac
 done
