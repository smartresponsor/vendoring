#!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
export COMMANDING_DIR

# shellcheck source=/dev/null
source "$COMMANDING_DIR/lib/ui.sh"

policy_file_primary="$COMMANDING_DIR/policy/dot-accept.yaml"
policy_file_secondary="$COMMANDING_DIR/.sandbox/policy/dot-accept.yaml"

parse_accept_list() {
  local file=''
  for file in "$policy_file_primary" "$policy_file_secondary"; do
    [ -f "$file" ] || continue
    sed -n 's/^[[:space:]]*-[[:space:]]*\(\.[A-Za-z0-9._-]*\).*/\1/p' "$file" | tr -d '\r'
    return 0
  done
  return 1
}

fallback_accept_list() {
  local root="$1"
  find "$root" -mindepth 1 -maxdepth 1 -type d -name '.*' \
    ! -name '.git' ! -name '.github' ! -name '.idea' ! -name '.sandbox' \
    -printf '%f\n' | sort
}

detect_entry() {
  local d="$1"
  local f=''
  for f in run.ps1 run.sh gate.ps1 gate.sh commanding.ps1 commanding.sh; do
    if [ -f "$d/$f" ]; then
      printf '%s' "$f"
      return 0
    fi
  done
  printf ''
  return 0
}

run_entry() {
  local root="$1"
  local name="$2"
  local d="$root/$name"
  local entry
  entry="$(detect_entry "$d")"
  if [ -z "$entry" ]; then
    printf '%s\n' "NONRUN: $name (no run.ps1/run.sh/gate.*/commanding.*)"
    return 0
  fi

  printf '\n%s\n' "RUN: $name ($entry)"
  printf '%s\n' '--------------------------------'

  case "$entry" in
    *.ps1)
      if command -v pwsh >/dev/null 2>&1; then
        (cd "$d" && pwsh -NoProfile -ExecutionPolicy Bypass -File "./$entry") || true
      elif command -v powershell >/dev/null 2>&1; then
        (cd "$d" && powershell -NoProfile -ExecutionPolicy Bypass -File "./$entry") || true
      else
        printf '%s\n' 'PowerShell not found.'
      fi
      ;;
    *.sh)
      (cd "$d" && bash "./$entry") || true
      ;;
    *)
      printf '%s\n' "Unsupported entry: $entry"
      ;;
  esac

  return 0
}

main() {
  local root key sel name ent i idx
  root="$(repo_root || true)"
  [ -n "${root:-}" ] || { ui_clear; ui_banner 'Dot'; printf '%s\n' 'Repo not detected.'; return 0 2>/dev/null || exit 0; }

  mapfile -t accept < <(parse_accept_list || fallback_accept_list "$root")

  items=()
  entries=()
  for name in "${accept[@]}"; do
    if [ -d "$root/$name" ]; then
      items+=("$name")
      entries+=("$(detect_entry "$root/$name")")
    fi
  done

  while true; do
    ui_clear
    ui_banner 'Dot'

    printf '%s\n\n' 'Accepted list (existing only):'

    if [ "${#items[@]}" -eq 0 ]; then
      printf '%s\n\n' 'None found in repo root.'
      printf '%s\n' 'q) Back'
      key="$(ui_pick_key)"
      [ -z "${key:-}" ] && return 0 2>/dev/null || exit 0
      [[ "$key" =~ [qQ] ]] && return 0 2>/dev/null || exit 0
      continue
    fi

    for ((i=0; i<${#items[@]}; i++)); do
      idx=$((i+1))
      name="${items[$i]}"
      ent="${entries[$i]}"
      if [ -n "$ent" ]; then
        printf '%s\n' " ${idx}) ${name}  [RUN]"
      else
        printf '%s\n' " ${idx}) ${name}  [NONRUN]"
      fi
    done

    printf '\n'
    printf '%s\n' 'a) Run all RUN items'
    printf '%s\n' 'r) Refresh'
    printf '%s\n' 'q) Back'
    printf '%s'   'Choice: '

    key="$(ui_pick_key)"
    printf '\n'

    [ -z "${key:-}" ] && return 0 2>/dev/null || exit 0

    case "$key" in
      a|A)
        for ((i=0; i<${#items[@]}; i++)); do
          name="${items[$i]}"
          ent="${entries[$i]}"
          [ -n "$ent" ] || continue
          ui_clear
          ui_banner 'Dot'
          run_entry "$root" "$name"
          ui_pause_any
        done
        ;;
      r|R)
        entries=()
        for name in "${items[@]}"; do
          entries+=("$(detect_entry "$root/$name")")
        done
        ;;
      q|Q)
        return 0 2>/dev/null || exit 0
        ;;
      [1-9])
        sel=$((key-1))
        if [ $sel -ge 0 ] && [ $sel -lt ${#items[@]} ]; then
          name="${items[$sel]}"
          ui_clear
          ui_banner 'Dot'
          run_entry "$root" "$name"
          ui_pause_any
        fi
        ;;
      *) ;;
    esac
  done
}

main "$@"
