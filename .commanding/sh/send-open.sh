#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"

json_get() {
  local file="$1"
  local key="$2"
  [ -f "$file" ] || return 1
  python3 - "$file" "$key" <<'PY'
import json,sys
path,key=sys.argv[1:3]
with open(path,'r',encoding='utf-8') as f:
    data=json.load(f)
value=data
for part in key.split('.'):
    if isinstance(value, dict):
        value=value.get(part,'')
    else:
        value=''
        break
if isinstance(value, (dict,list)):
    import json as _json
    print(_json.dumps(value, indent=2))
else:
    print(value)
PY
}

latest_bundle_from_prepare() {
  local prep_file
  prep_file="$(commanding_agent_prepare_state_file)"
  [ -f "$prep_file" ] || return 1
  json_get "$prep_file" bundle_dir
}

open_target_file() {
  local mode="$1"
  local prep_file send_file path
  prep_file="$(commanding_agent_prepare_state_file)"
  send_file="$(commanding_send_ui_state_file)"
  case "$mode" in
    payload)
      [ -f "$prep_file" ] || { ui_error 'No agent prepare state found.'; return 1; }
      path="$(json_get "$prep_file" payload_file || true)"
      ;;
    manifest)
      [ -f "$prep_file" ] || { ui_error 'No agent prepare state found.'; return 1; }
      path="$(json_get "$prep_file" attachments_file || true)"
      ;;
    request)
      [ -f "$send_file" ] || { ui_error 'No SendUI state found.'; return 1; }
      path="$(json_get "$send_file" request_file || true)"
      if [ -z "$path" ]; then
        local bundle
        bundle="$(json_get "$send_file" bundle_dir || true)"
        [ -n "$bundle" ] && path="$bundle/send-ui-request.json"
      fi
      ;;
    log)
      [ -f "$send_file" ] || { ui_error 'No SendUI state found.'; return 1; }
      path="$(json_get "$send_file" log_file || true)"
      ;;
    result)
      [ -f "$send_file" ] || { ui_error 'No SendUI state found.'; return 1; }
      path="$(json_get "$send_file" result_file || true)"
      ;;
    bundle)
      path="$(latest_bundle_from_prepare || true)"
      [ -n "$path" ] || { ui_error 'No prepared bundle found.'; return 1; }
      ;;
    *)
      ui_error "Unknown open mode: $mode"
      return 1
      ;;
  esac

  [ -n "$path" ] || { ui_error "Resolved empty path for mode: $mode"; return 1; }
  if [ -d "$path" ]; then
    ui_note "$path"
    return 0
  fi
  show_file "$path"
}

main() {
  local mode="${1:-}"
  [ -n "$mode" ] || {
    ui_error 'Usage: ./commanding.sh send:open-{payload|manifest|request|log|result|bundle}'
    exit 1
  }
  open_target_file "$mode"
}

main "$@"
