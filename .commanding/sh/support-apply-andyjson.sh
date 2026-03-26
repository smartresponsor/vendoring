#!/usr/bin/env bash
set -euo pipefail
TARGET="${1:-}"; if [[ -z "$TARGET" ]]; then echo "Usage: $0 <target_repo_root>"; exit 1; fi
KIT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PAYLOAD="$KIT_DIR/payload"
mkdir -p "$TARGET"; cd "$TARGET"
[[ -d .git ]] || git init
git checkout -B master || true
git config user.name "Oleksandr Tishchenko"
git config user.email "17111337+taa0662621456@users.noreply.github.com"
find "$PAYLOAD" -maxdepth 1 -mindepth 1 -type d | sort | while read -r STEP; do
  rsync -a --exclude ".git" "$STEP"/ ./
  MSG="Import $(basename "$STEP")"; DATE=""
  if [[ -f "$STEP/report/COMMIT.json" ]]; then
    MSG=$(python3 - <<'PY'
import json,os; step=os.environ.get("STEP",""); j=os.path.join(step,"report","COMMIT.json")
try: d=json.load(open(j,encoding="utf-8")); print(d.get("message") or ("Import "+os.path.basename(step)))
except: print("Import "+os.path.basename(step))
PY
); DATE=$(python3 - <<'PY'
import json,os; step=os.environ.get("STEP",""); j=os.path.join(step,"report","COMMIT.json")
try: d=json.load(open(j,encoding="utf-8")); print(d.get("when") or "")
except: print("")
PY
); fi
  export STEP="$STEP"
  export GIT_AUTHOR_NAME="Oleksandr Tishchenko"; export GIT_AUTHOR_EMAIL="17111337+taa0662621456@users.noreply.github.com"
  export GIT_COMMITTER_NAME="$GIT_AUTHOR_NAME"; export GIT_COMMITTER_EMAIL="$GIT_AUTHOR_EMAIL"
  [[ -n "$DATE" ]] && export GIT_AUTHOR_DATE="$DATE" GIT_COMMITTER_DATE="$DATE" || true
  git add -A; git commit -m "$MSG" --allow-empty || true
done
echo "Done."
