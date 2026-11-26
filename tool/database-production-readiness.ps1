Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
Push-Location $root
try {
    $php = 'C:\PHP\php-8.4.13-nts-Win32-vs17-x64\php.exe'
    if (-not (Test-Path $php)) { $php = 'php' }

    function Run([string[]] $Arguments) {
        & $php @Arguments
        if ($LASTEXITCODE -ne 0) {
            throw "Command failed ($LASTEXITCODE): php $($Arguments -join ' ')"
        }
    }

    Run @('tests/bin/transaction-migration-smoke.php')
    Run @('tests/bin/transaction-schema-parity-smoke.php')
    Run @('tests/bin/transaction-uniqueness-contract-smoke.php')
    Run @('vendor/bin/phpunit', '--configuration', 'phpunit.xml.dist', '--testsuite', 'unit', '--filter', 'VendorPayout')
    Run @('vendor/bin/phpunit', '--configuration', 'phpunit.xml.dist', '--testsuite', 'unit', '--filter', 'VendorTransaction')
}
finally {
    Pop-Location
}
