#!/usr/bin/env bash
set -euo pipefail

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/common.sh"

PROJECT_ROOT="$(local_server_project_root)"
HOST="$(local_server_host)"
PORT="$(local_server_port)"

local_server_prepare_runtime_dirs "$PROJECT_ROOT"
cd "$PROJECT_ROOT"

exec php -S "${HOST}:${PORT}" -t public public/index.php
