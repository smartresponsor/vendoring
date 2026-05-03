#requires -Version 7.2

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

# Vendoring: history cleanup (remove phase snapshots / fast-import baggage)
# Requires: git + git-filter-repo

function Assert-Cmd([string]$Name) {
  if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
    throw "Required command not found in PATH: $Name"
  }
}

Assert-Cmd git
Assert-Cmd git-filter-repo

& git rev-parse --git-dir *> $null
if ($LASTEXITCODE -ne 0) {
  throw "Run this script from inside a git repository."
}

# Safety: refuse to run on a dirty working tree.
& git diff --quiet
$dirty1 = $LASTEXITCODE
& git diff --cached --quiet
$dirty2 = $LASTEXITCODE
if ($dirty1 -ne 0 -or $dirty2 -ne 0) {
  throw "Working tree is not clean. Commit/stash changes first."
}

$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$safetyTag = "vendor-history-pre-filter-$stamp"

& git tag $safetyTag *> $null

Write-Host "Rewriting history... (safety tag: $safetyTag)"

& git filter-repo --force `
  --invert-paths `
  --path-glob 'src/src/**' `
  --path-glob 'src/**/src/**' `
  --path-glob 'src/**/vendor-current/**' `
  --path-glob 'src/**/vendor-sketch*/**' `
  --path-glob 'src/**/vendor-phase*/**' `
  --path-glob 'src/**/vendor-bucket*/**' `
  --path-glob 'src/**/[0-9][0-9][0-9]_*vendor-*/**' `
  --path-glob 'src/**/[0-9][0-9][0-9]-vendor-*/**' `
  --path-glob '.commanding/legacy/fast-import/**'

Write-Host 'Done. Review the repository, then force-push to a NEW branch or NEW remote.'
