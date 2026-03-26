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

main() {
  local bundle_dir="${1:-}"
  [ -n "$bundle_dir" ] || bundle_dir="$(latest_support_bundle || true)"
  [ -n "$bundle_dir" ] || { ui_error 'No support bundle found. Run ./commanding.sh support:bundle first.'; exit 1; }
  [ -d "$bundle_dir" ] || { ui_error "Bundle dir not found: $bundle_dir"; exit 1; }

  local summary_file raw_file snapshot_file touched_file payload_file attachments_file protocol_file note_file
  summary_file="$bundle_dir/summary.md"
  raw_file="$bundle_dir/raw.log"
  snapshot_file="$bundle_dir/snapshot.zip"
  touched_file="$bundle_dir/touched-files.zip"
  payload_file="$bundle_dir/chat-payload.md"
  attachments_file="$bundle_dir/attachments.manifest.json"
  protocol_file="$bundle_dir/delivery-protocol.md"
  note_file="$bundle_dir/agent-note.txt"

  cat > "$protocol_file" <<'MD'
# Delivery Protocol
- Deliver results in two flat archives.
- Archive 1: touched files.
- Archive 2: cumulative snapshot.
- Every subsequent wave must use the previous cumulative snapshot as the working base.
- Prefer factual analysis from the attached current slice.
MD

  cat > "$note_file" <<'TXT'
Use the attached support bundle to analyze the current failure or requested operation. Preserve existing progress and respond with touched-files and cumulative-snapshot deliveries for patch waves.
TXT

  cat > "$payload_file" <<MD
Analysis/support request for the current repository slice.

Context:
- Profile: $(resolve_execution_profile)
- Subject root: $(resolve_subject_root)
- Target root: $(resolve_target_root)
- Bound chat: $(chat_binding_url 2>/dev/null || printf not-bound)

Artifacts attached:
- $(basename "$summary_file")
- $(basename "$raw_file")
- $(basename "$snapshot_file")
$( [ -f "$touched_file" ] && printf -- '- %s\n' "$(basename "$touched_file")" )- $(basename "$protocol_file")

Working protocol:
- Return patch deliveries as two flat archives: touched files and cumulative snapshot.
- Use the attached cumulative/current slice as the factual base.
- Do not assume stale repository state outside the bundle.
MD

  python3 - "$attachments_file" "$summary_file" "$raw_file" "$snapshot_file" "$touched_file" "$protocol_file" "$note_file" "$payload_file" <<'PY'
import json, os, sys
out=sys.argv[1]
paths=sys.argv[2:]
files=[]
for p in paths:
    if p and os.path.isfile(p):
        files.append({"name": os.path.basename(p), "path": os.path.abspath(p)})
with open(out,'w',encoding='utf-8') as f:
    json.dump({"protocol":"commanding-agent-prepare-v1","files":files}, f, indent=2)
PY

  python3 - "$(commanding_agent_prepare_state_file)" "$bundle_dir" "$payload_file" "$attachments_file" <<'PY'
import json, os, sys, datetime
out,bundle,payload,manifest=sys.argv[1:5]
with open(out,'w',encoding='utf-8') as f:
    json.dump({
      'protocol':'commanding-agent-prepare-state-v1',
      'prepared_at':datetime.datetime.now().astimezone().isoformat(),
      'bundle_dir':os.path.abspath(bundle),
      'payload_file':os.path.abspath(payload),
      'attachments_file':os.path.abspath(manifest)
    }, f, indent=2)
PY

  ui_note "Agent payload prepared: $bundle_dir"
  ui_note "Payload: $payload_file"
  ui_note "Attachments manifest: $attachments_file"
}

main "$@"
