#!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

repo_root() {
  if git rev-parse --show-toplevel >/dev/null 2>&1; then
    git rev-parse --show-toplevel 2>/dev/null
    return 0
  fi

  if [ -n "${COMMANDING_DIR:-}" ] && git -C "$COMMANDING_DIR" rev-parse --show-toplevel >/dev/null 2>&1; then
    git -C "$COMMANDING_DIR" rev-parse --show-toplevel 2>/dev/null
    return 0
  fi

  return 1
}

detect_project_root() {
  local root=""
  root="$(repo_root || true)"
  if [ -n "$root" ]; then
    printf '%s\n' "$root"
    return 0
  fi

  if [ -n "${COMMANDING_DIR:-}" ]; then
    printf '%s\n' "$COMMANDING_DIR"
    return 0
  fi

  pwd
}

resolve_execution_profile() {
  if [ -n "${COMMANDING_PROFILE:-}" ]; then
    printf '%s\n' "$COMMANDING_PROFILE"
    return 0
  fi

  if [ -n "${WATCHDOG_PROFILE:-}" ]; then
    printf '%s\n' "$WATCHDOG_PROFILE"
    return 0
  fi

  if [ -n "${COMMANDING_DIR:-}" ] && [ "$(basename "$COMMANDING_DIR")" = ".commanding" ]; then
    printf '%s\n' 'application-centric'
    return 0
  fi

  if [ -n "${COMMANDING_DIR:-}" ] && [ -d "$COMMANDING_DIR/.sandbox" ]; then
    printf '%s\n' 'tool-centric'
    return 0
  fi

  printf '%s\n' 'standalone'
}

resolve_subject_root() {
  if [ -n "${COMMANDING_SUBJECT_ROOT:-}" ]; then
    printf '%s\n' "$COMMANDING_SUBJECT_ROOT"
    return 0
  fi

  if [ -n "${WATCHDOG_SUBJECT_ROOT:-}" ]; then
    printf '%s\n' "$WATCHDOG_SUBJECT_ROOT"
    return 0
  fi

  if [ -n "${COMMANDING_DIR:-}" ] && [ "$(basename "$COMMANDING_DIR")" = ".commanding" ]; then
    dirname "$COMMANDING_DIR"
    return 0
  fi

  if [ -n "${COMMANDING_DIR:-}" ]; then
    printf '%s\n' "$COMMANDING_DIR"
    return 0
  fi

  detect_project_root
}

resolve_target_root() {
  if [ -n "${COMMANDING_TARGET_ROOT:-}" ]; then
    printf '%s\n' "$COMMANDING_TARGET_ROOT"
    return 0
  fi

  if [ -n "${WATCHDOG_TARGET_ROOT:-}" ]; then
    printf '%s\n' "$WATCHDOG_TARGET_ROOT"
    return 0
  fi

  if [ -n "${WATCHDOG_PROVING_ROOT:-}" ]; then
    printf '%s\n' "$WATCHDOG_PROVING_ROOT"
    return 0
  fi

  if [ -n "${COMMANDING_DIR:-}" ] && [ "$(basename "$COMMANDING_DIR")" = ".commanding" ]; then
    dirname "$COMMANDING_DIR"
    return 0
  fi

  if [ -n "${COMMANDING_DIR:-}" ] && [ -d "$COMMANDING_DIR/.sandbox" ]; then
    printf '%s\n' "$COMMANDING_DIR/.sandbox"
    return 0
  fi

  resolve_subject_root
}

ensure_runtime_dirs() {
  mkdir -p "$COMMANDING_DIR/logs/runtime"
}

runtime_log_file() {
  printf '%s\n' "$COMMANDING_DIR/logs/runtime/actions.log"
}

runtime_error_file() {
  printf '%s\n' "$COMMANDING_DIR/logs/runtime/error.log"
}

action_log_file() {
  runtime_log_file
}

target_console_bin() {
  local target
  target="$(resolve_target_root)"
  printf '%s\n' "$target/bin/console"
}

target_phpunit_bin() {
  local target
  target="$(resolve_target_root)"
  printf '%s\n' "$target/vendor/bin/phpunit"
}

target_composer_json() {
  local target
  target="$(resolve_target_root)"
  printf '%s\n' "$target/composer.json"
}

target_phpunit_config() {
  local target
  target="$(resolve_target_root)"
  if [ -f "$target/phpunit.xml" ]; then
    printf '%s\n' "$target/phpunit.xml"
    return 0
  fi
  if [ -f "$target/phpunit.xml.dist" ]; then
    printf '%s\n' "$target/phpunit.xml.dist"
    return 0
  fi
  return 1
}

target_has_console() {
  [ -f "$(target_console_bin)" ]
}

target_has_phpunit() {
  [ -f "$(target_phpunit_bin)" ]
}

target_has_composer() {
  [ -f "$(target_composer_json)" ]
}

target_has_composer_script() {
  local script_name="${1:-}"
  local composer_json=""
  composer_json="$(target_composer_json)"
  command_exists php || return 1
  [ -f "$composer_json" ] || return 1
  php -r '''$file = $argv[1]; $script = $argv[2]; if (!is_file($file)) { exit(1); } $data = json_decode(file_get_contents($file), true); if (!is_array($data) || !isset($data["scripts"]) || !is_array($data["scripts"])) { exit(2); } exit(array_key_exists($script, $data["scripts"]) ? 0 : 3);''' "$composer_json" "$script_name" >/dev/null 2>&1
}

run_in_target() {
  local target
  target="$(resolve_target_root)"
  (
    cd "$target"
    "$@"
  )
}

run_logged_in_target() {
  local label="$1"
  shift
  local target
  target="$(resolve_target_root)"
  (
    cd "$target"
    run_logged "$label" "$@"
  )
}

non_interactive_snapshot_name() {
  printf 'commanding-support-%s' "$(date +%Y-%m-%d-%H-%M-%S)"
}

pick_existing_file() {
  local candidate=""
  for candidate in "$@"; do
    if [ -n "$candidate" ] && [ -f "$candidate" ]; then
      printf '%s\n' "$candidate"
      return 0
    fi
  done
  return 1
}

json_escape() {
  local value="${1:-}"
  value=${value//\\/\\\\}
  value=${value//\"/\\\"}
  value=${value//$'\n'/\\n}
  value=${value//$'\r'/\\r}
  value=${value//$'\t'/\\t}
  printf '%s' "$value"
}

json_bool() {
  if [ "${1:-0}" = "1" ]; then
    printf 'true'
  else
    printf 'false'
  fi
}

emit_json_result() {
  local status="${1:-ok}"
  local component="${2:-commanding}"
  local detail="${3:-}"
  local project_root=""
  project_root="$(resolve_target_root)"
  printf '{"status":"%s","component":"%s","detail":"%s","project_root":"%s"}\n' \
    "$(json_escape "$status")" \
    "$(json_escape "$component")" \
    "$(json_escape "$detail")" \
    "$(json_escape "$project_root")"
}

log_action() {
  ensure_runtime_dirs
  local log_file
  log_file="$(runtime_log_file)"
  printf '[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" >> "$log_file"
}

log_error() {
  ensure_runtime_dirs
  local error_file
  error_file="$(runtime_error_file)"
  printf '[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" >> "$error_file"
}

run_logged() {
  if [ "$#" -lt 2 ]; then
    ui_error "run_logged requires a label and a command."
    return 2
  fi

  local label="$1"
  shift

  ensure_runtime_dirs
  local error_file
  error_file="$(runtime_error_file)"

  log_action "$label"

  if "$@" 2>>"$error_file"; then
    log_action "$label [ok]"
    return 0
  fi

  local exit_code=$?
  log_action "$label [exit:$exit_code]"
  return "$exit_code"
}

show_file() {
  local path="$1"
  if [ ! -f "$path" ]; then
    ui_warn "File not found: $path"
    return 1
  fi

  if command -v less >/dev/null 2>&1; then
    less "$path"
    return 0
  fi

  cat "$path"
}

ui_status_line() {
  local label="$1"
  local value="$2"
  printf ' %-10s %s\n' "$label" "$value"
}

ui_runtime_status_block() {
  local php_status='missing'
  local composer_status='missing'
  local console_status='missing'
  local phpunit_status='missing'
  local docker_status='missing'

  command_exists php && php_status='ok'
  command_exists composer && composer_status='ok'
  target_has_console && console_status='ok'
  target_has_phpunit && phpunit_status='ok'
  command_exists docker && docker_status='ok'

  printf ' Status\n'
  printf ' ------\n'
  ui_status_line 'PHP:' "$php_status"
  ui_status_line 'Composer:' "$composer_status"
  ui_status_line 'Console:' "$console_status"
  ui_status_line 'PHPUnit:' "$phpunit_status"
  ui_status_line 'Docker:' "$docker_status"
}

ui_banner() {
  local title="${1:-Commanding}"
  local root=""
  local branch=""
  local profile=""
  local subject_root=""
  local target_root=""

  root="$(repo_root || true)"
  profile="$(resolve_execution_profile)"
  subject_root="$(resolve_subject_root)"
  target_root="$(resolve_target_root)"
  if [ -n "$root" ] && git -C "$root" rev-parse --abbrev-ref HEAD >/dev/null 2>&1; then
    branch="$(git -C "$root" rev-parse --abbrev-ref HEAD 2>/dev/null || true)"
  fi

  printf '\n'
  printf ' %s\n' "$title"
  printf ' %s\n' "$(printf '%.0s-' $(seq 1 ${#title}))"
  printf ' Repo: %s\n' "${root:-not resolved}"
  printf ' Profile: %s\n' "$profile"
  printf ' Subject: %s\n' "$subject_root"
  printf ' Target: %s\n' "$target_root"
  if [ -n "$branch" ]; then
    printf ' Branch: %s\n' "$branch"
  fi
  printf '\n'
  ui_runtime_status_block
  printf '\n'
}

ui_pause_any() {
  local msg="${1:-Press any key to continue...}"
  IFS= read -rsn1 -p "$msg" _ 2>/dev/null || true
  printf '\n'
  return 0
}

ui_clear() {
  clear || true
}

ui_pick_key() {
  local k=""
  IFS= read -rsn1 k 2>/dev/null || true
  if [[ "$k" == $'\n' || "$k" == $'\r' || "$k" == ' ' ]]; then
    printf ''
    return 0
  fi
  printf '%s' "$k"
}

ui_read_choice() {
  local first='' k='' buf=''

  if [ ! -t 0 ]; then
    return 1
  fi

  IFS= read -rsn1 first 2>/dev/null || return 1

  if [[ "$first" == $'\n' || "$first" == $'\r' || "$first" == ' ' ]]; then
    printf ''
    return 0
  fi

  if [[ "$first" =~ [0-9] ]]; then
    buf="$first"
    while IFS= read -rsn1 -t 0.20 k 2>/dev/null; do
      if [[ "$k" =~ [0-9] ]]; then
        buf+="$k"
        continue
      fi
      if [[ "$k" == $'\n' || "$k" == $'\r' ]]; then
        break
      fi
      break
    done
    printf '%s' "$buf"
    return 0
  fi

  printf '%s' "$first"
}

ui_action_success() {
  return 0
}

ui_action_failure() {
  local status="${1:-1}"
  ui_error "Command failed (exit=$status)"
  printf '%s\n' 'Tip: run ./commanding.sh support:bundle to prepare an agent-ready log bundle.' >&2
  ui_pause_any
  return 0
}

ui_complete_action() {
  local status="${1:-0}"
  if [ "$status" -ne 0 ]; then
    ui_action_failure "$status"
    return 0
  fi
  ui_action_success
  return 0
}

ui_hold_if_failed() {
  local status="${1:-0}"
  if [ "$status" -ne 0 ]; then
    ui_pause_any
  fi
  return 0
}

ui_note() {
  printf '%s\n' "$*"
}

ui_warn() {
  printf 'Warning: %s\n' "$*" >&2
}

ui_error() {
  printf 'Error: %s\n' "$*" >&2
}

ui_confirm() {
  local prompt="${1:-Are you sure? [y/N]: }"
  local answer=""
  read -r -p "$prompt" answer || true
  case "$answer" in
    y|Y|yes|YES)
      return 0
      ;;
    *)
      return 1
      ;;
  esac
}

require_command() {
  local command_name="$1"
  if command -v "$command_name" >/dev/null 2>&1; then
    return 0
  fi

  ui_error "Required command is not available: $command_name"
  return 1
}

command_exists() {
  command -v "$1" >/dev/null 2>&1
}

require_file() {
  local file_path="$1"
  if [ -f "$file_path" ]; then
    return 0
  fi

  ui_error "Required file is not available: $file_path"
  return 1
}


commanding_state_root() {
  local subject
  subject="$(resolve_subject_root)"
  printf '%s\n' "$subject/var/commanding/state"
}

commanding_ui_root() {
  local subject
  subject="$(resolve_subject_root)"
  printf '%s\n' "$subject/var/commanding/ui"
}

ensure_commanding_state_dirs() {
  mkdir -p "$(commanding_state_root)" "$(commanding_ui_root)"
}

chat_binding_file() {
  ensure_commanding_state_dirs
  printf '%s\n' "$(commanding_state_root)/chat-binding.json"
}

normalize_chat_url_or_id() {
  local value="${1:-}"
  [ -n "$value" ] || return 1
  case "$value" in
    https://chatgpt.com/c/*|http://chatgpt.com/c/*|https://chat.openai.com/c/*|http://chat.openai.com/c/*)
      printf '%s\n' "$value"
      return 0
      ;;
  esac
  if [[ "$value" =~ ^[A-Za-z0-9-]+$ ]]; then
    printf 'https://chatgpt.com/c/%s\n' "$value"
    return 0
  fi
  return 1
}

chat_id_from_url() {
  local value="${1:-}"
  value="${value%%\?*}"
  value="${value%%#*}"
  case "$value" in
    */c/*) printf '%s\n' "${value##*/c/}" ;;
    *) printf '%s\n' "$value" ;;
  esac
}

chat_binding_exists() {
  [ -f "$(chat_binding_file)" ]
}

chat_binding_url() {
  local file=""
  file="$(chat_binding_file)"
  [ -f "$file" ] || return 1
  command_exists python3 || return 1
  python3 - "$file" <<'PY2'
import json,sys
with open(sys.argv[1],'r',encoding='utf-8') as f:
    data=json.load(f)
print(data.get('chat_url',''))
PY2
}

chat_binding_id() {
  local file=""
  file="$(chat_binding_file)"
  [ -f "$file" ] || return 1
  command_exists python3 || return 1
  python3 - "$file" <<'PY2'
import json,sys
with open(sys.argv[1],'r',encoding='utf-8') as f:
    data=json.load(f)
print(data.get('chat_id',''))
PY2
}

commanding_agent_prepare_state_file() {
  ensure_commanding_state_dirs
  printf '%s\n' "$(commanding_state_root)/agent-prepare.json"
}

commanding_send_ui_state_file() {
  ensure_commanding_state_dirs
  printf '%s\n' "$(commanding_state_root)/send-ui.json"
}
