#!/usr/bin/env bash
set -euo pipefail
COMMANDING_DIR="${COMMANDING_DIR:-"$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"}"
source "$COMMANDING_DIR/sh/health-lib.sh"
health_require_target_phpunit
run_in_target php "$(target_phpunit_bin)" -c "$(target_phpunit_config)" >/dev/null
