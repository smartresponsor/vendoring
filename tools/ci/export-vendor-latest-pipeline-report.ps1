[CmdletBinding()]
param(
    [string]$DestinationZip = ''
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$pipelineRoot = Join-Path $projectRoot 'report\pipeline'

if (-not (Test-Path -LiteralPath $pipelineRoot)) {
    throw 'report\\pipeline directory does not exist'
}

$latestRun = Get-ChildItem -LiteralPath $pipelineRoot -Directory |
    Where-Object { $_.Name -match '^\d{8}-\d{6}$' } |
    Sort-Object Name -Descending |
    Select-Object -First 1

if ($null -eq $latestRun) {
    throw 'No timestamped pipeline runs found under report\\pipeline'
}

if ([string]::IsNullOrWhiteSpace($DestinationZip)) {
    $DestinationZip = Join-Path $pipelineRoot 'Vendoring-pipeline-latest.zip'
}

if (Test-Path -LiteralPath $DestinationZip) {
    Remove-Item -LiteralPath $DestinationZip -Force
}

Compress-Archive -Path (Join-Path $latestRun.FullName '*') -DestinationPath $DestinationZip -Force
Write-Host ("Exported latest pipeline report from {0} to {1}" -f $latestRun.FullName, $DestinationZip)
