#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/sh/health-lib.sh"

mode="${1:-$(health_target_mode)}"
filter_csv="$(health_latest_failed_probe_ids_csv || true)"
if [ -z "$filter_csv" ]; then
  ui_warn 'No failed probes found in latest health run.'
  exit 1
fi

bash "$COMMANDING_DIR/sh/health-sandbox-sweep.sh" "$mode" "$filter_csv"
