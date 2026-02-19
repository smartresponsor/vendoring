#requires -Version 7.2

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Assert-Cmd([string]$Name) {
  if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
    throw "Required command not found in PATH: $Name"
  }
}

Assert-Cmd git

& git rev-parse --git-dir *> $null
if ($LASTEXITCODE -ne 0) {
  throw "Run this script from inside a git repository."
}

$outDir = Join-Path (Get-Location) 'report/git-log'
New-Item -ItemType Directory -Force -Path $outDir *> $null

$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'

$graph = Join-Path $outDir "log-graph-$stamp.txt"
$stat  = Join-Path $outDir "log-stat-$stamp.txt"

& git log --graph --decorate --oneline --all | Out-File -Encoding utf8 $graph
& git log --decorate --date=iso --pretty=format:'%H %ad %D`n%s`n' --stat --all | Out-File -Encoding utf8 $stat

Write-Host "Wrote: $graph"
Write-Host "Wrote: $stat"
