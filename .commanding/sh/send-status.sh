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
    print(_json.dumps(value))
else:
    print(value)
PY
}

status_of_path() {
  local path="$1"
  if [ -f "$path" ]; then
    printf 'ok'
  else
    printf 'missing'
  fi
}

main() {
  local prep_file send_file binding_file payload_file manifest_file send_result_file send_log_file send_request_file
  prep_file="$(commanding_agent_prepare_state_file)"
  send_file="$(commanding_send_ui_state_file)"
  binding_file="$(chat_binding_file)"

  ui_banner 'SendUI Status'
  printf '%s\n' ' Prepare'
  printf '%s\n' ' -------'
  if [ -f "$prep_file" ]; then
    payload_file="$(json_get "$prep_file" payload_file || true)"
    manifest_file="$(json_get "$prep_file" attachments_file || true)"
    ui_status_line 'Prepared at:' "$(json_get "$prep_file" prepared_at || printf unknown)"
    ui_status_line 'Bundle:' "$(json_get "$prep_file" bundle_dir || printf unknown)"
    ui_status_line 'Payload:' "$payload_file"
    ui_status_line 'Payload file:' "$(status_of_path "$payload_file")"
    ui_status_line 'Manifest:' "$manifest_file"
    ui_status_line 'Manifest file:' "$(status_of_path "$manifest_file")"
  else
    ui_status_line 'Prepared:' 'none'
  fi
  printf '\n'
  printf '%s\n' ' Send'
  printf '%s\n' ' ----'
  if [ -f "$send_file" ]; then
    send_result_file="$(json_get "$send_file" result_file || true)"
    send_log_file="$(json_get "$send_file" log_file || true)"
    send_request_file="$(json_get "$send_file" request_file || true)"
    [ -n "$send_request_file" ] || send_request_file="$(json_get "$send_file" bundle_dir || true)/send-ui-request.json"
    ui_status_line 'Status:' "$(json_get "$send_file" status || printf unknown)"
    ui_status_line 'Finished at:' "$(json_get "$send_file" finished_at || printf unknown)"
    ui_status_line 'Bundle:' "$(json_get "$send_file" bundle_dir || printf unknown)"
    ui_status_line 'Request:' "$send_request_file"
    ui_status_line 'Request file:' "$(status_of_path "$send_request_file")"
    ui_status_line 'Result:' "$send_result_file"
    ui_status_line 'Result file:' "$(status_of_path "$send_result_file")"
    ui_status_line 'Log:' "$send_log_file"
    ui_status_line 'Log file:' "$(status_of_path "$send_log_file")"
  else
    ui_status_line 'Send:' 'none'
  fi
  printf '\n'
  printf '%s\n' ' Chat'
  printf '%s\n' ' ----'
  if [ -f "$binding_file" ]; then
    ui_status_line 'Chat ID:' "$(chat_binding_id || printf unknown)"
    ui_status_line 'Chat URL:' "$(chat_binding_url || printf unknown)"
  else
    ui_status_line 'Bound chat:' 'none'
  fi
  printf '\n'
  printf '%s\n' ' Shortcuts'
  printf '%s\n' ' ---------'
  printf '%s\n' ' ./commanding.sh send:doctor'
  printf '%s\n' ' ./commanding.sh send:retry'
  printf '%s\n' ' ./commanding.sh send:open-payload'
  printf '%s\n' ' ./commanding.sh send:open-manifest'
  printf '%s\n' ' ./commanding.sh send:open-request'
  printf '%s\n' ' ./commanding.sh send:open-log'
  printf '%s\n' ' ./commanding.sh send:open-result'
}

main "$@"
