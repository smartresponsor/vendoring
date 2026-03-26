#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"

file="$(chat_binding_file)"
if [ ! -f "$file" ]; then
  ui_warn 'No chat binding configured.'
  exit 1
fi
ui_banner 'Chat Binding'
ui_status_line 'Binding file:' "$file"
ui_status_line 'Chat ID:' "$(chat_binding_id || printf unknown)"
ui_status_line 'Chat URL:' "$(chat_binding_url || printf unknown)"
