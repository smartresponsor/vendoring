#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"

support_root() {
  local subject
  subject="$(resolve_subject_root)"
  printf '%s\n' "$subject/var/commanding/support"
}

write_context_json() {
  local path="$1"
  local target profile subject root branch php_v composer_ok console_ok phpunit_ok
  target="$(resolve_target_root)"
  profile="$(resolve_execution_profile)"
  subject="$(resolve_subject_root)"
  root="$(repo_root || true)"
  branch=''
  if [ -n "$root" ] && git -C "$root" rev-parse --abbrev-ref HEAD >/dev/null 2>&1; then
    branch="$(git -C "$root" rev-parse --abbrev-ref HEAD 2>/dev/null || true)"
  fi
  php_v='missing'
  command_exists php && php_v="$(php -r 'echo PHP_VERSION;' 2>/dev/null || printf ok)"
  composer_ok=0; target_has_composer && composer_ok=1
  console_ok=0; target_has_console && console_ok=1
  phpunit_ok=0; target_has_phpunit && phpunit_ok=1
  cat > "$path" <<JSON
{
  "protocol": "commanding-agent-support-v1",
  "generated_at": "$(date -Iseconds)",
  "profile": "$(json_escape "$profile")",
  "repo_root": "$(json_escape "$root")",
  "subject_root": "$(json_escape "$subject")",
  "target_root": "$(json_escape "$target")",
  "branch": "$(json_escape "$branch")",
  "php_version": "$(json_escape "$php_v")",
  "composer_present": $(json_bool "$composer_ok"),
  "console_present": $(json_bool "$console_ok"),
  "phpunit_present": $(json_bool "$phpunit_ok")
}
JSON
}

write_summary_md() {
  local path="$1" action_hint="$2"
  local root subject target profile branch action_log error_log latest_health failures_file
  root="$(repo_root || true)"
  subject="$(resolve_subject_root)"
  target="$(resolve_target_root)"
  profile="$(resolve_execution_profile)"
  branch=''
  if [ -n "$root" ] && git -C "$root" rev-parse --abbrev-ref HEAD >/dev/null 2>&1; then
    branch="$(git -C "$root" rev-parse --abbrev-ref HEAD 2>/dev/null || true)"
  fi
  action_log="$(runtime_log_file)"
  error_log="$(runtime_error_file)"
  latest_health="$(find "$subject/var/commanding/health" -mindepth 1 -maxdepth 1 -type d 2>/dev/null | sort | tail -n 1 || true)"
  failures_file=''
  [ -n "$latest_health" ] && [ -f "$latest_health/failures.txt" ] && failures_file="$latest_health/failures.txt"
  cat > "$path" <<MD
# Commanding Agent Support Bundle

## Protocol
- Deliver analysis results in **two flat archives**.
- Archive 1: **touched files**.
- Archive 2: **cumulative snapshot**.
- Every subsequent wave must use the previous cumulative snapshot as the working base.
- This support bundle is evidence/support material, not the final patch delivery itself.

## Context
- Generated at: $(date -Iseconds)
- Requested action hint: ${action_hint:-manual support bundle}
- Profile: $profile
- Repo root: ${root:-not resolved}
- Subject root: $subject
- Target root: $target
- Branch: ${branch:-not resolved}

## Attached bundle contents
- Raw log: raw.log
- Context: context.json
- Snapshot: snapshot.zip
- Touched files archive: touched-files.zip (only if changed files exist)

## Current operational logs
- Action log: $action_log
- Error log: $error_log

## Latest health sweep
- Latest health dir: ${latest_health:-not found}
- Failures file: ${failures_file:-not found}

## Notes for the agent
- Prefer factual analysis based on the attached snapshot.
- Do not assume stale repository state outside this bundle.
- Preserve existing progress and build from the cumulative base when preparing the next wave.
MD
}

copy_raw_log() {
  local out="$1"
  local action_log error_log
  action_log="$(runtime_log_file)"
  error_log="$(runtime_error_file)"
  {
    printf '=== ACTION LOG ===\n'
    [ -f "$action_log" ] && cat "$action_log"
    printf '\n=== ERROR LOG ===\n'
    [ -f "$error_log" ] && cat "$error_log"
  } > "$out"
}

build_snapshot_zip() {
  local out="$1"
  local root
  root="$(resolve_subject_root)"
  command_exists python3 || { ui_error 'python3 is required to build snapshot.zip'; return 1; }
  python3 - "$root" "$out" <<'PY'
import os, sys, zipfile
root = os.path.abspath(sys.argv[1])
out = os.path.abspath(sys.argv[2])
exclude_prefixes = [
    '.git/', 'vendor/', 'node_modules/', '.idea/', '.phpunit.cache/', 'coverage/', 'dist/', 'build/',
    'var/cache/', 'var/log/', 'var/commanding/support/', 'var/commanding/patch/',
    '.sandbox/vendor/', '.sandbox/var/cache/', '.sandbox/var/log/'
]
exclude_exact = {'.git', 'vendor', 'node_modules', '.idea', '.phpunit.cache', 'coverage', 'dist', 'build'}
with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED) as z:
    for base, dirs, files in os.walk(root):
        rel_base = os.path.relpath(base, root)
        rel_base = '' if rel_base == '.' else rel_base.replace('\\', '/')
        kept = []
        for d in dirs:
            rel = f"{rel_base}/{d}".strip('/')
            if d in exclude_exact:
                continue
            if any(rel == p.rstrip('/') or rel.startswith(p) for p in exclude_prefixes):
                continue
            kept.append(d)
        dirs[:] = kept
        for f in files:
            rel = f"{rel_base}/{f}".strip('/')
            if any(rel == p.rstrip('/') or rel.startswith(p) for p in exclude_prefixes):
                continue
            z.write(os.path.join(base, f), rel)
PY
}

build_touched_zip() {
  local out="$1"
  local root
  root="$(repo_root || true)"
  [ -n "$root" ] || return 1
  command_exists zip || return 1
  cd "$root"
  mapfile -t unstaged < <(git diff --name-only --diff-filter=ACMRT 2>/dev/null || true)
  mapfile -t staged < <(git diff --cached --name-only --diff-filter=ACMRT 2>/dev/null || true)
  declare -A seen=()
  local files=() f=''
  add_file() {
    local path="$1"
    [ -n "$path" ] || return 0
    [ -f "$path" ] || return 0
    if [ -z "${seen[$path]+x}" ]; then
      seen["$path"]=1
      files+=("$path")
    fi
  }
  for f in "${staged[@]}"; do add_file "$f"; done
  for f in "${unstaged[@]}"; do add_file "$f"; done
  [ "${#files[@]}" -gt 0 ] || return 1
  zip -q -r "$out" -- "${files[@]}"
}

main() {
  local action_hint="${1:-manual}"
  local base_dir bundle_dir summary_path context_path raw_path snapshot_path touched_path
  base_dir="$(support_root)"
  mkdir -p "$base_dir"
  bundle_dir="$base_dir/$(date +%Y-%m-%d-%H-%M-%S)"
  mkdir -p "$bundle_dir"
  summary_path="$bundle_dir/summary.md"
  context_path="$bundle_dir/context.json"
  raw_path="$bundle_dir/raw.log"
  snapshot_path="$bundle_dir/snapshot.zip"
  touched_path="$bundle_dir/touched-files.zip"

  write_context_json "$context_path"
  copy_raw_log "$raw_path"
  build_snapshot_zip "$snapshot_path"
  build_touched_zip "$touched_path" || true
  write_summary_md "$summary_path" "$action_hint"

  ui_note "Support bundle created: $bundle_dir"
  ui_note "Summary: $summary_path"
  ui_note "Raw log: $raw_path"
  ui_note "Snapshot: $snapshot_path"
  if [ -f "$touched_path" ]; then
    ui_note "Touched files: $touched_path"
  fi
}

main "$@"
