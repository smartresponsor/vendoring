#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"
while true; do
  ui_clear; ui_banner "Quality"
  printf '%s
' '1) Tests' '2) Composer' '3) Inspection' '4) Smoke category' '5) Smoke projection' '6) CS run' '7) Regression' '' 'Space) Back'
  printf '%s' 'Choice: '
  action="$(ui_read_choice || true)"; printf '

'
  case "${action:-}" in
    ''|' ') exit 0 ;;
    1) bash "$COMMANDING_DIR/sh/test.sh" ;;
    2) bash "$COMMANDING_DIR/sh/composer.sh" ;;
    3) bash "$COMMANDING_DIR/sh/inspection.sh" ;;
    4) bash "$COMMANDING_DIR/sh/smoke-category.sh" ;;
    5) bash "$COMMANDING_DIR/sh/smoke-projection.sh" ;;
    6) bash "$COMMANDING_DIR/sh/qa-cs-run.sh" ;;
    7) bash "$COMMANDING_DIR/sh/test-category-regression.sh" ;;
    *) ui_warn "Unknown action: $action"; ui_pause_any ;;
  esac
 done
