#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/sh/health-lib.sh"

mode="${1:-$(health_target_mode)}"

prompt_and_read() {
  printf '%s' 'Probe ids (comma-separated, empty=back): '
  ui_read_choice || true
}

while true; do
  ui_clear
  ui_banner "Health / Run selected probes (${mode})"
  printf '%s\n' 'Available probes:'
  while IFS='|' read -r probe_id probe_script; do
    [ -n "$probe_id" ] || continue
    printf ' - %s\n' "$probe_id"
  done < <(health_probe_entries_for_mode "$mode")
  printf '\n'
  selection="$(prompt_and_read)"
  printf '\n\n'
  case "${selection:-}" in
    ''|' '|0|q|Q)
      exit 0
      ;;
  esac

  if ! health_validate_probe_filter "$mode" "$selection"; then
    ui_warn 'One or more probe ids are unknown for this mode.'
    ui_pause_any
    continue
  fi

  bash "$COMMANDING_DIR/sh/health-sandbox-sweep.sh" "$mode" "$selection"
  exit $?
done
