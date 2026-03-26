# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
# Source-of-truth: root script. Embedded dot copies are projections.
[CmdletBinding()]
param(
    [string]$RepoDir = "",
    [string]$CommandingSh = ""
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Resolve-RepoDir([string]$Value) {
    if (-not [string]::IsNullOrWhiteSpace($Value)) {
        return (Resolve-Path -LiteralPath $Value).Path
    }

    return (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '.')).Path
}

function Resolve-CommandingSh([string]$RepoRoot, [string]$Value) {
    if (-not [string]::IsNullOrWhiteSpace($Value)) {
        return $Value
    }

    if (Test-Path -LiteralPath (Join-Path $RepoRoot 'commanding.sh') -PathType Leaf) {
        return 'commanding.sh'
    }

    if (Test-Path -LiteralPath (Join-Path $RepoRoot '.commanding/commanding.sh') -PathType Leaf) {
        return '.commanding/commanding.sh'
    }

    throw "No commanding shell entrypoint found in: $RepoRoot"
}

function Find-GitBash() {
    $candidates = @(
        "$env:ProgramFiles\Git\bin\bash.exe",
        "$env:ProgramFiles\Git\usr\bin\bash.exe",
        "$env:ProgramW6432\Git\bin\bash.exe",
        "$env:ProgramW6432\Git\usr\bin\bash.exe",
        "$env:LocalAppData\Programs\Git\bin\bash.exe",
        "$env:LocalAppData\Programs\Git\usr\bin\bash.exe"
    )

    foreach ($candidate in $candidates) {
        if (-not [string]::IsNullOrWhiteSpace($candidate) -and (Test-Path -LiteralPath $candidate -PathType Leaf)) {
            return $candidate
        }
    }

    return $null
}

function Test-CommandAvailable([string]$Name) {
    return $null -ne (Get-Command $Name -ErrorAction SilentlyContinue)
}

$resolvedRepoDir = Resolve-RepoDir $RepoDir
$resolvedCommandingSh = Resolve-CommandingSh $resolvedRepoDir $CommandingSh
$repoName = Split-Path -Leaf $resolvedRepoDir
$gitBash = Find-GitBash
$hasWt = Test-CommandAvailable 'wt.exe'
$hasWsl = Test-CommandAvailable 'wsl.exe'
$bashCommand = "if [ -f $resolvedCommandingSh ]; then exec bash -i $resolvedCommandingSh; else exec bash -i; fi"

if ($gitBash) {
    if ($hasWt) {
        Start-Process -WindowStyle Minimized -FilePath 'wt.exe' -ArgumentList @('-w','0','new-tab','--title',$repoName,'-d',$resolvedRepoDir,'--',$gitBash,'-lc',$bashCommand)
        exit 0
    }

    Start-Process -WorkingDirectory $resolvedRepoDir -FilePath $gitBash -ArgumentList @('-lc', $bashCommand)
    exit 0
}

if ($hasWsl) {
    $wslPath = (& wsl.exe -- wslpath -a $resolvedRepoDir).Trim()
    $wslCommand = "cd \"$wslPath\"; $bashCommand"

    if ($hasWt) {
        Start-Process -WindowStyle Minimized -FilePath 'wt.exe' -ArgumentList @('-w','0','new-tab','--title',$repoName,'wsl.exe','--','bash','-lc',$wslCommand)
        exit 0
    }

    Start-Process -FilePath 'wsl.exe' -ArgumentList @('--','bash','-lc',$wslCommand)
    exit 0
}

Write-Error 'No terminal runtime found. Install Git for Windows or enable WSL.'
