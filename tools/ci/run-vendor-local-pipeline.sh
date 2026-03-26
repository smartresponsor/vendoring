#!/usr/bin/env bash
set -u

INCLUDE_SMOKES=0
INCLUDE_REPORTS=0
FAIL_ON_ERRORS=0

for arg in "$@"; do
  case "$arg" in
    --include-smokes) INCLUDE_SMOKES=1 ;;
    --include-reports) INCLUDE_REPORTS=1 ;;
    --fail-on-errors) FAIL_ON_ERRORS=1 ;;
  esac
done

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
REPORT_ROOT="$PROJECT_ROOT/report/pipeline/$TIMESTAMP"
LOGS_ROOT="$REPORT_ROOT/logs"

mkdir -p "$LOGS_ROOT"

declare -a STEP_NAMES
declare -a STEP_COMMANDS

add_step() {
  STEP_NAMES+=("$1")
  STEP_COMMANDS+=("$2")
}

run_step() {
  local name="$1"
  local command="$2"
  local log_path="$3"

  echo
  echo "==> $name"
  echo "    $command"

  local start end duration exit_code status
  start="$(date +%s)"
  bash -lc "$command" >"$log_path" 2>&1
  exit_code=$?
  end="$(date +%s)"
  duration=$((end - start))

  if [ "$exit_code" -eq 0 ]; then
    status="passed"
  else
    status="failed"
  fi

  RESULTS+=("$status|$name|$exit_code|$duration|$log_path")
}

add_step "composer-validate" "composer validate --no-check-publish"
add_step "lint" "composer lint"
add_step "cs-check" "composer cs:check"
add_step "phpstan" "composer stan"
add_step "phpmd-src" "composer md"
add_step "phpmd-tests" "composer md:tests"
add_step "phpunit" "composer test"

if [ "$INCLUDE_SMOKES" -eq 1 ]; then
  add_step "smoke-runtime" "composer smoke:runtime"
  add_step "smoke-container" "composer smoke:container"
  add_step "smoke-doctrine" "composer smoke:doctrine"
  add_step "smoke-fixtures" "composer smoke:fixtures"
  add_step "smoke-fixture-load" "composer smoke:fixture-load"
  add_step "smoke-admin" "composer smoke:admin"
fi

if [ "$INCLUDE_REPORTS" -eq 1 ]; then
  add_step "report-canonical-structure" "composer report:canonical-structure"
  add_step "report-mirror-enforcer" "composer report:mirror-enforcer"
  add_step "report-config-guard" "composer report:config-guard"
  add_step "report-config-drift" "composer report:config-drift"
  add_step "report-php-surface" "composer report:php-surface"
  add_step "report-prod-marker" "composer report:prod-marker"
  add_step "report-quality-residue" "composer report:quality-residue"
  add_step "report-contract" "composer report:contract"
  add_step "report-readiness" "composer report:readiness"
fi

RESULTS=()

for i in "${!STEP_NAMES[@]}"; do
  safe_name="$(echo "${STEP_NAMES[$i]}" | sed 's/[^a-zA-Z0-9._-]/-/g')"
  run_step "${STEP_NAMES[$i]}" "${STEP_COMMANDS[$i]}" "$LOGS_ROOT/$safe_name.log"
done

PASSED=0
FAILED=0

for row in "${RESULTS[@]}"; do
  status="${row%%|*}"
  if [ "$status" = "passed" ]; then
    PASSED=$((PASSED + 1))
  else
    FAILED=$((FAILED + 1))
  fi
done

SUMMARY_TXT="$REPORT_ROOT/summary.txt"
SUMMARY_MD="$REPORT_ROOT/summary.md"
SUMMARY_JSON="$REPORT_ROOT/summary.json"

{
  echo "Vendoring local pipeline"
  echo "Timestamp: $TIMESTAMP"
  echo "Report root: $REPORT_ROOT"
  echo "Passed: $PASSED"
  echo "Non-passed: $FAILED"
  echo "Total: ${#RESULTS[@]}"
  echo
  for row in "${RESULTS[@]}"; do
    IFS='|' read -r status name exit_code duration log <<< "$row"
    echo "[${status^^}] $name (exit=$exit_code, duration=${duration}s)"
    echo "  log: $log"
  done
} > "$SUMMARY_TXT"

{
  echo "# Vendoring local pipeline"
  echo
  echo "- Timestamp: \`$TIMESTAMP\`"
  echo "- Report root: \`$REPORT_ROOT\`"
  echo "- Passed: **$PASSED**"
  echo "- Non-passed: **$FAILED**"
  echo "- Total: **${#RESULTS[@]}**"
  echo
  echo "| Status | Step | Exit | Duration (s) | Log |"
  echo "|---|---|---:|---:|---|"
  for row in "${RESULTS[@]}"; do
    IFS='|' read -r status name exit_code duration log <<< "$row"
    log_name="$(basename "$log")"
    echo "| $status | $name | $exit_code | $duration | \`$log_name\` |"
  done
} > "$SUMMARY_MD"

{
  echo "{"
  echo "  \"component\": \"Vendoring\"," 
  echo "  \"timestamp\": \"$TIMESTAMP\"," 
  echo "  \"report_root\": \"$REPORT_ROOT\"," 
  echo "  \"passed\": $PASSED,"
  echo "  \"non_passed\": $FAILED,"
  echo "  \"total\": ${#RESULTS[@]},"
  echo "  \"strict\": $([ "$FAIL_ON_ERRORS" -eq 1 ] && echo "true" || echo "false"),"
  echo "  \"include_smokes\": $([ "$INCLUDE_SMOKES" -eq 1 ] && echo "true" || echo "false"),"
  echo "  \"include_reports\": $([ "$INCLUDE_REPORTS" -eq 1 ] && echo "true" || echo "false"),"
  echo "  \"steps\": ["
  for i in "${!RESULTS[@]}"; do
    IFS='|' read -r status name exit_code duration log <<< "${RESULTS[$i]}"
    comma=","
    if [ "$i" -eq $((${#RESULTS[@]} - 1)) ]; then
      comma=""
    fi
    echo "    {\"name\":\"$name\",\"status\":\"$status\",\"exit_code\":$exit_code,\"duration_seconds\":$duration,\"log\":\"$log\"}$comma"
  done
  echo "  ]"
  echo "}"
} > "$SUMMARY_JSON"

echo
echo "Pipeline summary:"
echo "  report: $REPORT_ROOT"
echo "  passed: $PASSED / ${#RESULTS[@]}"
echo "  non-passed: $FAILED"

if [ "$FAIL_ON_ERRORS" -eq 1 ] && [ "$FAILED" -gt 0 ]; then
  exit 1
fi

exit 0
