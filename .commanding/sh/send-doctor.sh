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

manifest_missing_count() {
  local manifest="$1"
  [ -f "$manifest" ] || { printf '0'; return 0; }
  python3 - "$manifest" <<'PY'
import json, os, sys
with open(sys.argv[1],'r',encoding='utf-8') as f:
    data=json.load(f)
files=data.get('files',[])
missing=0
for item in files:
    path=item.get('path','')
    if not path or not os.path.isfile(path):
        missing += 1
print(missing)
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
  local prep_file send_file binding_file hook bundle payload manifest request result log chat_url hook_status manifest_status missing_count
  prep_file="$(commanding_agent_prepare_state_file)"
  send_file="$(commanding_send_ui_state_file)"
  binding_file="$(chat_binding_file)"
  hook="${COMMANDING_SEND_UI_HOOK:-}"

  ui_banner 'SendUI Doctor'

  printf '%s\n' ' Binding'
  printf '%s\n' ' -------'
  if [ -f "$binding_file" ]; then
    chat_url="$(chat_binding_url || true)"
    ui_status_line 'Binding file:' 'ok'
    ui_status_line 'Chat URL:' "${chat_url:-missing}"
  else
    ui_status_line 'Binding file:' 'missing'
  fi
  printf '\n'

  printf '%s\n' ' Hook'
  printf '%s\n' ' ----'
  if [ -n "$hook" ]; then
    if [ -x "$hook" ]; then
      hook_status='ok'
    elif [ -e "$hook" ]; then
      hook_status='not-executable'
    else
      hook_status='missing'
    fi
    ui_status_line 'COMMANDING_SEND_UI_HOOK:' "$hook"
    ui_status_line 'Hook status:' "$hook_status"
  else
    ui_status_line 'COMMANDING_SEND_UI_HOOK:' 'not set'
  fi
  printf '\n'

  printf '%s\n' ' Prepared bundle'
  printf '%s\n' ' ---------------'
  if [ -f "$prep_file" ]; then
    bundle="$(json_get "$prep_file" bundle_dir || true)"
    payload="$(json_get "$prep_file" payload_file || true)"
    manifest="$(json_get "$prep_file" attachments_file || true)"
    ui_status_line 'Prepared at:' "$(json_get "$prep_file" prepared_at || printf unknown)"
    ui_status_line 'Bundle dir:' "${bundle:-missing}"
    ui_status_line 'Payload file:' "$(status_of_path "$payload")"
    ui_status_line 'Manifest file:' "$(status_of_path "$manifest")"
    if [ -f "$manifest" ]; then
      missing_count="$(manifest_missing_count "$manifest")"
      if [ "$missing_count" = '0' ]; then
        manifest_status='ok'
      else
        manifest_status="missing-files:$missing_count"
      fi
      ui_status_line 'Manifest paths:' "$manifest_status"
    fi
  else
    ui_status_line 'Prepared state:' 'missing'
  fi
  printf '\n'

  printf '%s\n' ' Last send'
  printf '%s\n' ' ---------'
  if [ -f "$send_file" ]; then
    request="$(json_get "$send_file" request_file || true)"
    result="$(json_get "$send_file" result_file || true)"
    log="$(json_get "$send_file" log_file || true)"
    ui_status_line 'Status:' "$(json_get "$send_file" status || printf unknown)"
    ui_status_line 'Finished at:' "$(json_get "$send_file" finished_at || printf unknown)"
    ui_status_line 'Request file:' "$(status_of_path "$request")"
    ui_status_line 'Result file:' "$(status_of_path "$result")"
    ui_status_line 'Log file:' "$(status_of_path "$log")"
  else
    ui_status_line 'Send state:' 'missing'
  fi
  printf '\n'

  printf '%s\n' ' Verdict'
  printf '%s\n' ' -------'
  local rc=0
  if [ ! -f "$binding_file" ]; then
    ui_status_line 'Ready to send:' 'no (bind a chat first)'
    rc=1
  elif [ ! -f "$prep_file" ]; then
    ui_status_line 'Ready to send:' 'no (run agent:prepare first)'
    rc=1
  elif [ ! -f "$payload" ] || [ ! -f "$manifest" ]; then
    ui_status_line 'Ready to send:' 'no (payload/manifest missing)'
    rc=1
  elif [ -f "$manifest" ] && [ "$(manifest_missing_count "$manifest")" != '0' ]; then
    ui_status_line 'Ready to send:' 'no (manifest references missing files)'
    rc=1
  elif [ -n "$hook" ] && [ ! -x "$hook" ]; then
    ui_status_line 'Ready to send:' 'no (hook invalid)'
    rc=1
  else
    if [ -n "$hook" ]; then
      ui_status_line 'Ready to send:' 'yes (hook configured)'
    else
      ui_status_line 'Ready to send:' 'partial (request can be prepared, no hook configured)'
    fi
  fi

  exit "$rc"
}

main "$@"
