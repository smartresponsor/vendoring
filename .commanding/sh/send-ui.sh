#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"

support_root() {
  local subject
  subject="$(resolve_subject_root)"
  printf '%s\n' "$subject/var/commanding/support"
}

latest_support_bundle() {
  local base
  base="$(support_root)"
  [ -d "$base" ] || return 1
  find "$base" -mindepth 1 -maxdepth 1 -type d 2>/dev/null | sort | tail -n 1
}

latest_prepared_bundle() {
  local state_file
  state_file="$(commanding_agent_prepare_state_file)"
  [ -f "$state_file" ] || return 1
  python3 - "$state_file" <<'PY'
import json,sys
with open(sys.argv[1],'r',encoding='utf-8') as f:
    data=json.load(f)
print(data.get('bundle_dir',''))
PY
}

validate_manifest_files() {
  local manifest="$1"
  python3 - "$manifest" <<'PY'
import json, os, sys
with open(sys.argv[1],'r',encoding='utf-8') as f:
    data=json.load(f)
files=data.get('files',[])
missing=[]
for item in files:
    path=item.get('path','')
    if not path or not os.path.isfile(path):
        missing.append(path or item.get('name','<unknown>'))
if missing:
    print('\n'.join(missing))
    sys.exit(1)
PY
}

write_send_state() {
  local status="$1"
  local bundle_dir="$2"
  local result_file="$3"
  local log_file="$4"
  python3 - "$(commanding_send_ui_state_file)" "$status" "$bundle_dir" "$result_file" "$log_file" "$request_file" <<'PY'
import json, os, sys, datetime
out,status,bundle,result_file,log_file,request_file=sys.argv[1:7]
with open(out,'w',encoding='utf-8') as f:
    json.dump({
      'protocol':'commanding-send-ui-state-v1',
      'finished_at':datetime.datetime.now().astimezone().isoformat(),
      'status':status,
      'bundle_dir':os.path.abspath(bundle),
      'result_file':os.path.abspath(result_file),
      'log_file':os.path.abspath(log_file),
      'request_file':os.path.abspath(request_file)
    }, f, indent=2)
PY
}

main() {
  local bundle_dir="${1:-}"
  [ -n "$bundle_dir" ] || bundle_dir="$(latest_prepared_bundle || true)"
  [ -n "$bundle_dir" ] || bundle_dir="$(latest_support_bundle || true)"
  [ -n "$bundle_dir" ] || { ui_error 'No support bundle found. Run ./commanding.sh support:bundle first.'; exit 1; }
  [ -d "$bundle_dir" ] || { ui_error "Bundle dir not found: $bundle_dir"; exit 1; }

  local payload_file attachments_file binding_file bound_url request_file result_file log_file hook status missing_manifest
  payload_file="$bundle_dir/chat-payload.md"
  attachments_file="$bundle_dir/attachments.manifest.json"
  [ -f "$payload_file" ] || { ui_error 'chat-payload.md missing. Run ./commanding.sh agent:prepare first.'; exit 1; }
  [ -f "$attachments_file" ] || { ui_error 'attachments.manifest.json missing. Run ./commanding.sh agent:prepare first.'; exit 1; }
  set +e
  missing_manifest="$(validate_manifest_files "$attachments_file" 2>&1)"
  status=$?
  set -e
  if [ "$status" -ne 0 ]; then
    ui_error 'Attachments manifest contains missing files.'
    printf '%s\n' "$missing_manifest" >&2
    exit 1
  fi

  binding_file="$(chat_binding_file)"
  [ -f "$binding_file" ] || { ui_error 'No bound chat configured. Run ./commanding.sh chat:bind <url-or-id> first.'; exit 1; }
  bound_url="$(chat_binding_url || true)"
  [ -n "$bound_url" ] || { ui_error 'Bound chat URL could not be read.'; exit 1; }

  request_file="$bundle_dir/send-ui-request.json"
  result_file="$bundle_dir/send-ui-result.json"
  log_file="$bundle_dir/send-ui.log"
  hook="${COMMANDING_SEND_UI_HOOK:-}"

  python3 - "$request_file" "$bundle_dir" "$payload_file" "$attachments_file" "$binding_file" <<'PY'
import json, os, sys, datetime
out,bundle,payload,manifest,binding=sys.argv[1:6]
with open(binding,'r',encoding='utf-8') as f:
    bind=json.load(f)
with open(manifest,'r',encoding='utf-8') as f:
    manifest_data=json.load(f)
req={
  "protocol":"commanding-send-ui-v1",
  "generated_at":datetime.datetime.now().astimezone().isoformat(),
  "bundle_dir":os.path.abspath(bundle),
  "payload_file":os.path.abspath(payload),
  "chat_url":bind.get('chat_url',''),
  "chat_id":bind.get('chat_id',''),
  "attachments":manifest_data.get('files',[])
}
with open(out,'w',encoding='utf-8') as f:
    json.dump(req,f,indent=2)
PY

  {
    printf 'SendUI request prepared\n'
    printf 'Bundle: %s\n' "$bundle_dir"
    printf 'Chat: %s\n' "$bound_url"
    printf 'Payload: %s\n' "$payload_file"
    printf 'Manifest: %s\n' "$attachments_file"
    printf 'Request: %s\n' "$request_file"
  } > "$log_file"

  if [ -n "$hook" ]; then
    if [ ! -x "$hook" ]; then
      ui_error "COMMANDING_SEND_UI_HOOK is not executable: $hook"
      exit 1
    fi
    set +e
    "$hook" "$request_file" >> "$log_file" 2>&1
    status=$?
    set -e
    python3 - "$result_file" "$status" "$bound_url" "$request_file" <<'PY'
import json, sys, datetime
out,status,url,request=sys.argv[1:5]
with open(out,'w',encoding='utf-8') as f:
    json.dump({
      'protocol':'commanding-send-ui-result-v1',
      'finished_at':datetime.datetime.now().astimezone().isoformat(),
      'status':'success' if status == '0' else 'failure',
      'exit_code':int(status),
      'chat_url':url,
      'request_file':request
    }, f, indent=2)
PY
    if [ "$status" -eq 0 ]; then
      write_send_state success "$bundle_dir" "$result_file" "$log_file"
      ui_note 'SendUI completed via hook.'
      ui_note "Result: $result_file"
      exit 0
    fi
    write_send_state failure "$bundle_dir" "$result_file" "$log_file"
    ui_error "SendUI hook failed. See $log_file"
    exit "$status"
  fi

  cat > "$result_file" <<JSON
{
  "protocol": "commanding-send-ui-result-v1",
  "finished_at": "$(date -Iseconds)",
  "status": "pending-hook",
  "exit_code": 2,
  "chat_url": "$(json_escape "$bound_url")",
  "request_file": "$(json_escape "$request_file")"
}
JSON
  write_send_state pending-hook "$bundle_dir" "$result_file" "$log_file"
  ui_warn 'SendUI request prepared, but no UI hook is configured.'
  ui_note 'Set COMMANDING_SEND_UI_HOOK to an executable that consumes send-ui-request.json'
  ui_note "Example hook: $COMMANDING_DIR/sh/send-ui-hook-example.sh"
  ui_note "Request: $request_file"
  ui_note "Log: $log_file"
  exit 2
}

main "$@"
