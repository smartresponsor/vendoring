#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/sh/health-lib.sh"

mode="${1:-$(health_target_mode)}"
ui_clear
ui_banner "Health / Probe catalog (${mode})"
printf '%s\n' 'ID                       Script' '-----------------------  ----------------------------------------'
while IFS='|' read -r probe_id probe_script; do
  [ -n "$probe_id" ] || continue
  printf '%-24s %s\n' "$probe_id" "${probe_script#$COMMANDING_DIR/}"
done < <(health_probe_entries_for_mode "$mode")
printf '\n'
printf '%s\n' 'Empty/space = back'
ui_pause_any
