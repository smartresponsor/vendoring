#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"

while true; do
  ui_clear
  ui_banner 'Repository'
  printf '%s\n' ' 1) Patch bundle'
  printf '%s\n' ' 2) Support bundle'
  printf '%s\n' ' 3) Chat bind/show/clear'
  printf '%s\n' ' 4) Send status'
  printf '%s\n' ' Space) Back'
  printf '%s' ' Select: '
  choice="$(ui_read_choice || true)"
  printf '\n\n'
  case "$choice" in
    ''|' ') exit 0 ;;
    1) bash "$COMMANDING_DIR/sh/patch-zipper.sh" || true ;;
    2) bash "$COMMANDING_DIR/sh/support-bundle.sh" || true ;;
    3) bash "$COMMANDING_DIR/sh/chat-show.sh" || true ;;
    4) bash "$COMMANDING_DIR/sh/send-status.sh" || true ;;
    *) ui_warn "Unknown input: $choice"; ui_pause_any ;;
  esac
done
