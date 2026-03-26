#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/sh/health-lib.sh"

mode="${1:-sandbox}"
probe_filter_csv="${2:-}"
target_mode="$(health_target_mode)"
if [ "$mode" = 'sandbox' ] && [ "$target_mode" != 'sandbox' ]; then
  ui_error "Sandbox full sweep requires sandbox target. Resolved target: $(resolve_target_root)"
  exit 1
fi

mapfile -t probes < <(health_probe_entries_for_mode "$mode" "$probe_filter_csv")
if [ "${#probes[@]}" -eq 0 ]; then
  ui_error "No health probes selected for mode: $mode"
  exit 1
fi

started_epoch=$(date +%s)
stamp="$(date '+%Y%m%d-%H%M%S')"
run_dir="$(health_run_dir "$stamp")"
mkdir -p "$run_dir"
summary_file="$(health_summary_file "$run_dir")"
manifest_file="$(health_manifest_file "$run_dir")"
results_file="$(health_results_file "$run_dir")"
failures_file="$(health_failures_file "$run_dir")"

printf 'Health sweep\n' > "$summary_file"
printf 'Mode: %s\n' "$mode" >> "$summary_file"
printf 'Target: %s\n' "$(resolve_target_root)" >> "$summary_file"
printf 'Profile: %s\n' "$(resolve_execution_profile)" >> "$summary_file"
printf 'StartedAt: %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" >> "$summary_file"
if [ -n "$probe_filter_csv" ]; then
  printf 'Selection: %s\n' "$probe_filter_csv" >> "$summary_file"
fi
printf '\n' >> "$summary_file"
: > "$results_file"
: > "$manifest_file"
: > "$failures_file"

total="${#probes[@]}"
passed=0
failed=0
skipped=0
failed_names=()

ui_clear
ui_banner "Health / ${mode^} sweep"
ui_note "Run directory: $run_dir"
ui_note ''

index=0
for entry in "${probes[@]}"; do
  index=$((index + 1))
  probe_id="${entry%%|*}"
  probe_script="${entry#*|}"
  slug="$(health_slug "$probe_id")"
  log_file="$run_dir/${slug}.log"
  printf '%s|%s\n' "$probe_id" "$probe_script" >> "$manifest_file"

  health_progress_bar "$index" "$total"
  printf 'Running: %s\n' "$probe_id"
  printf 'Totals: OK=%s FAIL=%s SKIP=%s\n' "$passed" "$failed" "$skipped"

  if [ ! -f "$probe_script" ]; then
    failed=$((failed + 1))
    failed_names+=("$probe_id")
    printf 'missing probe script: %s\n' "$probe_script" > "$log_file"
    health_write_result "$results_file" "$probe_id" 'MISSING' 127 "$log_file"
    printf 'FAIL  %s\n' "$probe_id"
    printf 'FAIL  %s -> %s\n' "$probe_id" "$log_file" >> "$summary_file"
    printf '%s\n' "$probe_id" >> "$failures_file"
    printf '\n'
    continue
  fi

  set +e
  bash "$probe_script" >"$log_file" 2>&1
  status=$?
  set -e

  if [ "$status" -eq 0 ]; then
    passed=$((passed + 1))
    health_write_result "$results_file" "$probe_id" 'OK' 0 "$log_file"
    printf 'OK    %s\n' "$probe_id"
    printf 'OK    %s\n' "$probe_id" >> "$summary_file"
  else
    failed=$((failed + 1))
    failed_names+=("$probe_id")
    health_write_result "$results_file" "$probe_id" 'FAIL' "$status" "$log_file"
    printf 'FAIL  %s\n' "$probe_id"
    printf 'FAIL  %s -> %s\n' "$probe_id" "$log_file" >> "$summary_file"
    printf '%s\n' "$probe_id" >> "$failures_file"
  fi
  printf '\n'
done

finished_epoch=$(date +%s)
duration_seconds=$(( finished_epoch - started_epoch ))
printf 'Passed: %s\n' "$passed" >> "$summary_file"
printf 'Failed: %s\n' "$failed" >> "$summary_file"
printf 'Skipped: %s\n' "$skipped" >> "$summary_file"
printf 'DurationSeconds: %s\n' "$duration_seconds" >> "$summary_file"
printf 'FinishedAt: %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" >> "$summary_file"
printf 'Results: %s\n' "$results_file" >> "$summary_file"

printf '\nSummary\n-------\n'
printf 'Passed: %s\n' "$passed"
printf 'Failed: %s\n' "$failed"
printf 'Skipped: %s\n' "$skipped"

if [ "$failed" -gt 0 ]; then
  printf '\nFailures\n--------\n'
  for name in "${failed_names[@]}"; do
    printf '%s\n' "$name"
  done
  printf '\nLogs: %s\n' "$run_dir"
  exit 1
fi

printf '\nLogs: %s\n' "$run_dir"
exit 0
