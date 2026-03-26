#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"

file="$(chat_binding_file)"
if [ -f "$file" ]; then
  rm -f "$file"
  ui_note 'Chat binding cleared.'
else
  ui_warn 'No chat binding configured.'
fi
