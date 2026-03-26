#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/sh/health-lib.sh"

latest="$(health_latest_run_dir || true)"
[ -n "$latest" ] || { ui_warn 'No health runs found yet.'; exit 1; }
failures_file="$(health_failures_file "$latest")"
[ -s "$failures_file" ] || { ui_warn 'Latest health run has no failed probes.'; exit 1; }

mapfile -t failed_ids < "$failures_file"
ui_clear
ui_banner "Health / Failed probe log"
printf 'Latest run: %s

' "$latest"
index=0
for probe_id in "${failed_ids[@]}"; do
  index=$((index + 1))
  printf ' %s) %s
' "$index" "$probe_id"
done
printf '
Space) Exit
'
printf 'Choice: '
choice="$(ui_read_choice || true)"
printf '

'
case "${choice:-}" in
  ''|' '|0|q|Q) exit 0 ;;
esac
if ! [[ "$choice" =~ ^[0-9]+$ ]] || [ "$choice" -lt 1 ] || [ "$choice" -gt "${#failed_ids[@]}" ]; then
  ui_warn "Unknown failed probe selection: ${choice:-}"
  exit 1
fi
probe_id="${failed_ids[$((choice - 1))]}"
log_file="$(health_failure_log_for_probe "$latest" "$probe_id" || true)"
[ -n "$log_file" ] && [ -f "$log_file" ] || { ui_warn "Log file not found for probe: $probe_id"; exit 1; }
show_file "$log_file"
