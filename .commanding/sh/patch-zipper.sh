#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/lib/ui.sh"

repo_root_safe() {
  repo_root || true
}

main() {
  local root=""
  root="$(repo_root_safe)"
  [ -n "$root" ] || { ui_error 'Git repo not detected.'; return 1; }
  command_exists zip || { ui_error 'zip command is not available.'; return 1; }
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

  if [ "${#files[@]}" -eq 0 ]; then
    ui_warn 'No changed files detected.'
    return 0
  fi

  mkdir -p "$root/var/commanding/patch"
  local ts zip_name zip_path
  ts="$(date +%Y-%m-%d-%H-%M-%S)"
  zip_name="touched-files-${ts}.zip"
  zip_path="$root/var/commanding/patch/$zip_name"
  zip -q -r "$zip_path" -- "${files[@]}"
  ui_note "Touched-files archive created: $zip_path"
  ui_note "Files archived: ${#files[@]}"
}

main "$@"
