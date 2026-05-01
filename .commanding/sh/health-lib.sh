#!/usr/bin/env bash
set -euo pipefail

COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
# shellcheck source=/dev/null
source "$COMMANDING_DIR/lib/ui.sh"

health_root_dir() {
  printf '%s\n' "$COMMANDING_DIR/logs/health"
}

health_run_dir() {
  local stamp="${1:-}"
  [ -n "$stamp" ] || stamp="$(date '+%Y%m%d-%H%M%S')"
  printf '%s\n' "$(health_root_dir)/$stamp"
}

health_latest_run_dir() {
  local root
  root="$(health_root_dir)"
  [ -d "$root" ] || return 1
  find "$root" -mindepth 1 -maxdepth 1 -type d | sort | tail -n 1
}

health_target_is_sandbox() {
  local target
  target="$(resolve_target_root)"
  [ -d "$target" ] || return 1
  if [ "$(basename -- "$target")" = '.sandbox' ]; then
    return 0
  fi
  [ -f "$target/policy/sandbox-contract.yaml" ]
}

health_target_mode() {
  if health_target_is_sandbox; then
    printf '%s\n' 'sandbox'
    return 0
  fi
  printf '%s\n' 'application'
}

health_slug() {
  local value="${1:-probe}"
  value="${value,,}"
  value="${value// /-}"
  value="${value//_/-}"
  value="${value//:/-}"
  value="${value//\//-}"
  value="${value//./-}"
  value="${value//[^a-z0-9-]/}"
  printf '%s\n' "$value"
}

health_progress_bar() {
  local current="$1"
  local total="$2"
  local width=24
  local filled=0
  local empty=0
  local i=0
  [ "$total" -gt 0 ] || total=1
  filled=$(( current * width / total ))
  empty=$(( width - filled ))
  printf '['
  for ((i=0; i<filled; i++)); do printf '#'; done
  for ((i=0; i<empty; i++)); do printf '-'; done
  printf '] %s/%s\n' "$current" "$total"
}

health_require_target_console() {
  command_exists php || { ui_error 'PHP is not available in PATH'; return 1; }
  target_has_console || { ui_error "bin/console not found in target root: $(resolve_target_root)"; return 1; }
  return 0
}

health_require_target_phpunit() {
  command_exists php || { ui_error 'PHP is not available in PATH'; return 1; }
  target_has_phpunit || { ui_error "vendor/bin/phpunit not found in target root: $(resolve_target_root)"; return 1; }
  target_phpunit_config >/dev/null || { ui_error "phpunit.xml(.dist) not found in target root: $(resolve_target_root)"; return 1; }
  return 0
}

health_results_file() {
  local run_dir="$1"
  printf '%s\n' "$run_dir/results.tsv"
}

health_failures_file() {
  local run_dir="$1"
  printf '%s\n' "$run_dir/failures.txt"
}

health_summary_file() {
  local run_dir="$1"
  printf '%s\n' "$run_dir/summary.txt"
}

health_manifest_file() {
  local run_dir="$1"
  printf '%s\n' "$run_dir/manifest.txt"
}

health_write_result() {
  local results_file="$1"
  local probe_id="$2"
  local status_label="$3"
  local exit_code="$4"
  local log_file="$5"
  printf '%s\t%s\t%s\t%s\n' "$probe_id" "$status_label" "$exit_code" "$log_file" >> "$results_file"
}

health_collect_failed_probe_ids() {
  local run_dir="$1"
  local results_file
  local output_file
  results_file="$(health_results_file "$run_dir")"
  output_file="$(health_failures_file "$run_dir")"
  : > "$output_file"
  [ -f "$results_file" ] || return 0
  awk -F '\t' '$2 != "OK" { print $1 }' "$results_file" > "$output_file"
}

health_open_latest_failures() {
  local latest
  local failures_file
  latest="$(health_latest_run_dir || true)"
  if [ -z "$latest" ]; then
    ui_warn 'No health runs found yet.'
    return 1
  fi
  failures_file="$(health_failures_file "$latest")"
  if [ ! -s "$failures_file" ]; then
    ui_note 'Latest health run has no failed probes.'
    return 0
  fi
  show_file "$failures_file"
}

health_registry_for_mode() {
  local mode="${1:-sandbox}"
  if [ "$mode" = 'sandbox' ]; then
    cat <<'REGISTRY'
console|sh/health-probe-console.sh
sandbox-command|sh/health-probe-sandbox-command.sh
routes|sh/health-probe-routes.sh
cache-clear|sh/health-probe-cache-clear.sh
schema-validate|sh/health-probe-schema-validate.sh
migrations-status|sh/health-probe-migrations-status.sh
fixtures-load|sh/health-probe-fixtures-load.sh
phpunit|sh/health-probe-phpunit.sh
REGISTRY
    return 0
  fi

  cat <<'REGISTRY'
console|sh/health-probe-console.sh
routes|sh/health-probe-routes.sh
schema-validate|sh/health-probe-schema-validate.sh
migrations-status|sh/health-probe-migrations-status.sh
phpunit|sh/health-probe-phpunit.sh
REGISTRY
}

health_probe_script_for_id() {
  local mode="${1:-sandbox}"
  local probe_id="$2"
  local entry
  while IFS= read -r entry; do
    [ -n "$entry" ] || continue
    if [ "${entry%%|*}" = "$probe_id" ]; then
      printf '%s\n' "$COMMANDING_DIR/${entry#*|}"
      return 0
    fi
  done < <(health_registry_for_mode "$mode")
  return 1
}

health_probe_entries_for_mode() {
  local mode="${1:-sandbox}"
  local filter_csv="${2:-}"
  local entry probe_id
  while IFS= read -r entry; do
    [ -n "$entry" ] || continue
    probe_id="${entry%%|*}"
    if [ -n "$filter_csv" ] && [[ ",$filter_csv," != *",$probe_id,"* ]]; then
      continue
    fi
    printf '%s|%s\n' "$probe_id" "$COMMANDING_DIR/${entry#*|}"
  done < <(health_registry_for_mode "$mode")
}


health_probe_exists() {
  local mode="${1:-sandbox}"
  local probe_id="$2"
  health_probe_script_for_id "$mode" "$probe_id" >/dev/null 2>&1
}

health_validate_probe_filter() {
  local mode="${1:-sandbox}"
  local filter_csv="${2:-}"
  local probe_id
  [ -n "$filter_csv" ] || return 1
  filter_csv="${filter_csv// /}"
  IFS=',' read -r -a ids <<< "$filter_csv"
  for probe_id in "${ids[@]}"; do
    [ -n "$probe_id" ] || continue
    health_probe_exists "$mode" "$probe_id" || return 1
  done
  return 0
}

health_latest_failed_probe_ids_csv() {
  local latest
  local failures_file
  latest="$(health_latest_run_dir || true)"
  [ -n "$latest" ] || return 1
  failures_file="$(health_failures_file "$latest")"
  [ -s "$failures_file" ] || return 1
  paste -sd, "$failures_file"
}


health_results_count() {
  local run_dir="$1"
  local status_filter="$2"
  local results_file
  results_file="$(health_results_file "$run_dir")"
  [ -f "$results_file" ] || { printf '0
'; return 0; }
  awk -F '	' -v wanted="$status_filter" '$2 == wanted { count++ } END { print count+0 }' "$results_file"
}

health_run_duration_seconds() {
  local run_dir="$1"
  local summary_file
  summary_file="$(health_summary_file "$run_dir")"
  [ -f "$summary_file" ] || return 1
  awk -F ': ' '$1 == "DurationSeconds" { print $2; found=1 } END { if (!found) exit 1 }' "$summary_file"
}

health_latest_status_line() {
  local latest summary_file mode target passed failed skipped duration
  latest="$(health_latest_run_dir || true)"
  if [ -z "$latest" ]; then
    printf '%s
' 'Health: no runs yet'
    return 0
  fi
  summary_file="$(health_summary_file "$latest")"
  mode="$(awk -F ': ' '$1 == "Mode" { print $2; exit }' "$summary_file" 2>/dev/null || true)"
  target="$(awk -F ': ' '$1 == "Target" { print $2; exit }' "$summary_file" 2>/dev/null || true)"
  passed="$(health_results_count "$latest" 'OK')"
  failed="$(health_results_count "$latest" 'FAIL')"
  skipped="$(health_results_count "$latest" 'SKIP')"
  duration="$(health_run_duration_seconds "$latest" 2>/dev/null || true)"
  printf 'Health: latest=%s ok=%s fail=%s skip=%s' "${mode:-unknown}" "$passed" "$failed" "$skipped"
  if [ -n "$duration" ]; then
    printf ' duration=%ss' "$duration"
  fi
  if [ -n "$target" ]; then
    printf ' target=%s' "$target"
  fi
  printf '
'
}

health_failure_log_for_probe() {
  local run_dir="$1"
  local probe_id="$2"
  local results_file
  results_file="$(health_results_file "$run_dir")"
  [ -f "$results_file" ] || return 1
  awk -F '	' -v wanted="$probe_id" '$1 == wanted { print $4; found=1; exit } END { if (!found) exit 1 }' "$results_file"
}
