# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

<#
.SYNOPSIS
  Export a canonical ZIP from git-tracked files only (no empty dirs, no junk).

.DESCRIPTION
  Uses `git archive` so snapshots never include empty scaffold directories like src/src, Bridge/Bridge, etc.
  Optional: prune empty dirs in worktree before export (safe: empty dirs only).

.PARAMETER Out
  Output zip path. Default: vendoring-export-<timestamp>-<hash>.zip in repo root.

.PARAMETER PruneEmpty
  If set, runs tools/vendoring-prune-empty-dir.php --root=src before exporting.

.PARAMETER Strict
  If set, runs tools/vendoring-structure-scan.php --strict before exporting.
#>

param(
  [string]$Out = "",
  [switch]$PruneEmpty,
  [switch]$Strict
)

$ErrorActionPreference = 'Stop'

$repoRoot = (& git rev-parse --show-toplevel) 2>$null
if (-not $repoRoot) {
  throw "ERROR: not a git repository (git rev-parse failed)"
}

Set-Location $repoRoot

$hash = (& git rev-parse --short HEAD).Trim()
$ts = (Get-Date).ToString('yyyyMMddTHHmm')

if ([string]::IsNullOrWhiteSpace($Out)) {
  $Out = Join-Path $repoRoot "vendoring-export-$ts-$hash.zip"
}

if ($PruneEmpty) {
  & php tools/vendoring-prune-empty-dir.php --root=src | Write-Host
}

if ($Strict) {
  & php tools/vendoring-structure-scan.php --strict | Write-Host
}

# Canonical export: tracked files only.
& git archive --format=zip -o $Out HEAD

Write-Host "OK: exported $Out"
