#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"

main() {
  local input="${1:-}"
  if [ -z "$input" ]; then
    printf '%s' 'Chat URL or ID: '
    input="$(ui_read_choice || true)"
    printf '\n'
  fi
  [ -n "$input" ] || { ui_error 'Chat URL or ID is required.'; exit 1; }
  local url id file
  url="$(normalize_chat_url_or_id "$input" || true)"
  [ -n "$url" ] || { ui_error 'Could not normalize chat URL/ID.'; exit 1; }
  id="$(chat_id_from_url "$url")"
  [ -n "$id" ] || { ui_error 'Could not resolve chat id.'; exit 1; }
  file="$(chat_binding_file)"
  mkdir -p "$(dirname "$file")"
  cat > "$file" <<JSON
{
  "protocol": "commanding-chat-binding-v1",
  "bound_at": "$(date -Iseconds)",
  "chat_url": "$(json_escape "$url")",
  "chat_id": "$(json_escape "$id")"
}
JSON
  ui_note "Chat bound: $url"
  ui_note "Binding file: $file"
}

main "$@"
