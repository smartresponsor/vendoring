[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$HostAppPath
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repositoryRoot = Split-Path -Parent $PSScriptRoot
$hostDatabaseUrl = $null
foreach ($candidate in @('.env.local', '.env')) {
    $envFile = Join-Path $HostAppPath $candidate
    if (-not (Test-Path $envFile)) { continue }
    foreach ($line in Get-Content -LiteralPath $envFile) {
        if ($line -match '^DATABASE_URL=(.+)$') {
            $hostDatabaseUrl = $Matches[1].Trim().Trim('"').Trim("'")
            break
        }
    }
    if (-not [string]::IsNullOrWhiteSpace($hostDatabaseUrl)) { break }
}

if ([string]::IsNullOrWhiteSpace($hostDatabaseUrl)) {
    throw 'DATABASE_URL was not found in the Host App env files.'
}

$env:APP_ENV = 'fixtures'
$env:APP_DEBUG = '0'
$env:KERNEL_CLASS = 'App\Vendoring\Kernel'
$env:VENDOR_DSN = $hostDatabaseUrl

Push-Location $repositoryRoot
try {
    & php bin/console cache:clear --env=fixtures --no-interaction
    if ($LASTEXITCODE -ne 0) {
        throw "Vendoring fixtures cache clear failed with exit code $LASTEXITCODE."
    }

    & php bin/console app:vendor:fixtures:load --env=fixtures --no-interaction
    if ($LASTEXITCODE -ne 0) {
        throw "Vendoring Host fixture load failed with exit code $LASTEXITCODE."
    }
}
finally {
    Pop-Location
}

Write-Host 'Vendoring access bootstrap loaded into Host database.'
