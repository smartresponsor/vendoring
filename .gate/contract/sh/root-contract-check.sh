#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="${1:-.}"
while [[ "$REPO_ROOT" == */ && "$REPO_ROOT" != "/" ]]; do
  REPO_ROOT="${REPO_ROOT%/}"
done

fail=0
issues=()

required_files=(".gitignore" "README.md" "composer.json")
allowed_files=(".gitignore" "README.md" "composer.json")
allowed_dot_dir=(".canonization" ".commanding" ".consuming" ".gate" ".github" ".ide" ".intelligence")
allowed_non_dot_dir=("bin" "build" "config" "delivery" "deploy" "docs" "drivers" "migrations" "ops" "public" "src")
forbidden_root_dir=("Vendor" "tools" ".deploy" ".release" ".smoke" ".idea" "logs" "scripts")

is_allowed_root_file() {
  local name="$1"
  for f in "${allowed_files[@]}"; do
    if [[ "$name" == "$f" ]]; then
      return 0
    fi
  done
  return 1
}

is_forbidden_root_dir() {
  local name="$1"
  local lower_name="${name,,}"
  for f in "${forbidden_root_dir[@]}"; do
    if [[ "$lower_name" == "${f,,}" ]]; then
      return 0
    fi
  done
  return 1
}

is_allowed_root_dir() {
  local name="$1"
  if [[ "$name" == ".git" ]]; then
    return 0
  fi
  if is_forbidden_root_dir "$name"; then
    return 1
  fi
  if [[ "$name" == .* ]]; then
    for a in "${allowed_dot_dir[@]}"; do
      if [[ "$name" == "$a" ]]; then
        return 0
      fi
    done
    return 1
  fi
  for a in "${allowed_non_dot_dir[@]}"; do
    if [[ "$name" == "$a" ]]; then
      return 0
    fi
  done
  return 1
}

while IFS= read -r entry; do
  [[ -n "$entry" ]] || continue

  if [[ "$entry" == "." || "$entry" == ".." ]]; then
    continue
  fi

  if [[ "$entry" == ".git" ]]; then
    continue
  fi

  full="$REPO_ROOT/$entry"

  if [[ -d "$full" ]]; then
    if ! is_allowed_root_dir "$entry"; then
      issues+=(" - Unexpected root directory: $entry")
      fail=1
    fi
  else
    if ! is_allowed_root_file "$entry"; then
      issues+=(" - Unexpected root file: $entry")
      fail=1
    fi
  fi
done < <(cd "$REPO_ROOT" && ls -A)

for req in "${required_files[@]}"; do
  if [[ ! -f "$REPO_ROOT/$req" ]]; then
    issues+=(" - Missing required root file: $req")
    fail=1
  fi
done

if [[ "$fail" == "1" ]]; then
  echo ""
  echo "Root contract FAILED:"
  echo ""
  for i in "${issues[@]}"; do
    echo "$i"
  done

  mkdir -p "$REPO_ROOT/build/reports/gate"
  : > "$REPO_ROOT/build/reports/gate/root-contract.fail"

  exit 2
fi

echo "Root contract OK"
exit 0
