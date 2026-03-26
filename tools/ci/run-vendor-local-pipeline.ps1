[CmdletBinding()]
param(
    [switch]$IncludeSmokes,
    [switch]$IncludeReports,
    [switch]$FailOnErrors
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function New-DirectoryIfMissing {
    param([string]$Path)

    if (-not (Test-Path -LiteralPath $Path)) {
        New-Item -ItemType Directory -Path $Path -Force | Out-Null
    }
}

function Get-Timestamp {
    return (Get-Date).ToString('yyyyMMdd-HHmmss')
}

function Test-CommandPathAvailable {
    param([string]$Path)

    return Test-Path -LiteralPath (Join-Path $projectRoot $Path)
}

function Test-ExecutableAvailable {
    param([string]$Name)

    return $null -ne (Get-Command $Name -ErrorAction SilentlyContinue)
}

function Test-PathPatternAvailable {
    param([string]$Pattern)

    return @(Get-ChildItem -Path $projectRoot -Recurse -File -Filter $Pattern -ErrorAction SilentlyContinue).Count -gt 0
}

function Test-ComposerPackageInstalled {
    param([string]$PackageName)

    $lockPath = Join-Path $projectRoot 'composer.lock'
    if (-not (Test-Path -LiteralPath $lockPath)) {
        return $false
    }

    try {
        $lock = Get-Content -LiteralPath $lockPath -Raw | ConvertFrom-Json
    }
    catch {
        return $false
    }

    $packages = @()
    if ($null -ne $lock.packages) {
        $packages += $lock.packages
    }
    if ($null -ne $lock.'packages-dev') {
        $packages += $lock.'packages-dev'
    }

    return @($packages | Where-Object { $_.name -eq $PackageName }).Count -gt 0
}

function Invoke-Step {
    param(
        [string]$Name,
        [string]$Command,
        [string]$LogPath,
        [bool]$Skip,
        [string]$SkipReason
    )

    Write-Host ""
    Write-Host "==> $Name"

    if ($Skip) {
        Write-Host "    skipped: $SkipReason"
        Set-Content -LiteralPath $LogPath -Value ("SKIPPED: {0}" -f $SkipReason) -Encoding UTF8

        return [PSCustomObject]@{
            name = $Name
            command = $Command
            status = 'skipped'
            exit_code = 0
            duration_seconds = 0
            log = $LogPath
        }
    }

    Write-Host "    $Command"

    $start = Get-Date
    $stdoutPath = [System.IO.Path]::GetTempFileName()
    $stderrPath = [System.IO.Path]::GetTempFileName()
    $combinedOutput = ''
    $exitCode = 0
    $status = 'passed'

    try {
        $process = Start-Process -FilePath 'cmd.exe' -ArgumentList '/d', '/c', $Command -WorkingDirectory $projectRoot -NoNewWindow -Wait -PassThru -RedirectStandardOutput $stdoutPath -RedirectStandardError $stderrPath
        $exitCode = $process.ExitCode

        $stdout = ''
        $stderr = ''

        if (Test-Path -LiteralPath $stdoutPath) {
            $stdout = Get-Content -LiteralPath $stdoutPath -Raw -ErrorAction SilentlyContinue
        }

        if (Test-Path -LiteralPath $stderrPath) {
            $stderr = Get-Content -LiteralPath $stderrPath -Raw -ErrorAction SilentlyContinue
        }

        $combinedOutput = ($stdout, $stderr | Where-Object { $_ -ne $null -and $_ -ne '' }) -join [Environment]::NewLine
    }
    catch {
        $combinedOutput = ($_ | Out-String)
        $exitCode = 1
        $status = 'failed'
    }
    finally {
        Remove-Item -LiteralPath $stdoutPath -ErrorAction SilentlyContinue
        Remove-Item -LiteralPath $stderrPath -ErrorAction SilentlyContinue
    }

    if ($exitCode -ne 0 -and $status -eq 'passed') {
        $status = 'failed'
    }

    $duration = [Math]::Round(((Get-Date) - $start).TotalSeconds, 3)

    Set-Content -LiteralPath $LogPath -Value $combinedOutput -Encoding UTF8

    return [PSCustomObject]@{
        name = $Name
        command = $Command
        status = $status
        exit_code = $exitCode
        duration_seconds = $duration
        log = $LogPath
    }
}

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$timestamp = Get-Timestamp
$pipelineRoot = Join-Path $projectRoot 'report\pipeline'
$reportRoot = Join-Path $pipelineRoot $timestamp
$logsRoot = Join-Path $reportRoot 'logs'

New-DirectoryIfMissing -Path $pipelineRoot
New-DirectoryIfMissing -Path $reportRoot
New-DirectoryIfMissing -Path $logsRoot

$steps = @(
    @{ name = 'composer-validate'; command = 'composer validate --no-check-publish'; skip = $false; reason = '' },
    @{ name = 'lint'; command = 'composer lint'; skip = $false; reason = '' },
    @{ name = 'cs-check'; command = 'composer cs:check'; skip = $false; reason = '' },
    @{ name = 'phpstan'; command = 'composer stan'; skip = $false; reason = '' },
    @{ name = 'phpmd-src'; command = 'composer md'; skip = (-not (Test-ComposerPackageInstalled -PackageName 'phpmd/phpmd')); reason = 'phpmd/phpmd is not installed in composer.lock' },
    @{ name = 'phpmd-tests'; command = 'composer md:tests'; skip = (-not (Test-ComposerPackageInstalled -PackageName 'phpmd/phpmd')); reason = 'phpmd/phpmd is not installed in composer.lock' },
    @{ name = 'phpunit'; command = 'composer test'; skip = $false; reason = '' }
)

if ($IncludeSmokes) {
    $steps += @(
        @{ name = 'smoke-runtime'; command = 'composer smoke:runtime'; skip = (-not (Test-CommandPathAvailable -Path 'tools\smoke\vendor-runtime-smoke.php')); reason = 'tools\\smoke\\vendor-runtime-smoke.php is missing' },
        @{ name = 'smoke-container'; command = 'composer smoke:container'; skip = (-not (Test-CommandPathAvailable -Path 'tools\smoke\vendor-container-boot-smoke.php')); reason = 'tools\\smoke\\vendor-container-boot-smoke.php is missing' },
        @{ name = 'smoke-doctrine'; command = 'composer smoke:doctrine'; skip = (-not (Test-CommandPathAvailable -Path 'tools\smoke\vendor-doctrine-mapping-smoke.php')); reason = 'tools\\smoke\\vendor-doctrine-mapping-smoke.php is missing' },
        @{ name = 'smoke-admin'; command = 'composer smoke:admin'; skip = (-not (Test-CommandPathAvailable -Path 'tools\smoke\vendor-admin-smoke.php')); reason = 'tools\\smoke\\vendor-admin-smoke.php is missing' }
    )
}

if ($IncludeReports) {
    $steps += @(
        @{ name = 'report-canonical-structure'; command = 'composer report:canonical-structure'; skip = (-not (Test-CommandPathAvailable -Path 'tools\report\VendorCanonicalStructureReport.php')); reason = 'tools\\report\\VendorCanonicalStructureReport.php is missing' },
        @{ name = 'report-mirror-enforcer'; command = 'composer report:mirror-enforcer'; skip = (-not (Test-CommandPathAvailable -Path 'tools\report\VendorMirrorEnforcerReport.php')); reason = 'tools\\report\\VendorMirrorEnforcerReport.php is missing' },
        @{ name = 'report-config-guard'; command = 'composer report:config-guard'; skip = (-not (Test-CommandPathAvailable -Path 'tools\report\VendorConfigGuardReport.php')); reason = 'tools\\report\\VendorConfigGuardReport.php is missing' },
        @{ name = 'report-config-drift'; command = 'composer report:config-drift'; skip = (-not (Test-CommandPathAvailable -Path 'tools\report\VendorConfigDriftReport.php')); reason = 'tools\\report\\VendorConfigDriftReport.php is missing' },
        @{ name = 'report-php-surface'; command = 'composer report:php-surface'; skip = (-not (Test-CommandPathAvailable -Path 'tools\report\VendorPhpSurfaceReport.php')); reason = 'tools\\report\\VendorPhpSurfaceReport.php is missing' },
        @{ name = 'report-prod-marker'; command = 'composer report:prod-marker'; skip = (-not (Test-CommandPathAvailable -Path 'tools\report\VendorProductionMarkerReport.php')); reason = 'tools\\report\\VendorProductionMarkerReport.php is missing' },
        @{ name = 'report-quality-residue'; command = 'composer report:quality-residue'; skip = (-not (Test-CommandPathAvailable -Path 'tools\report\VendorQualityResidueReport.php')); reason = 'tools\\report\\VendorQualityResidueReport.php is missing' },
        @{ name = 'report-contract'; command = 'composer report:contract'; skip = (-not (Test-CommandPathAvailable -Path 'tools\report\VendorContractReport.php')); reason = 'tools\\report\\VendorContractReport.php is missing' },
        @{ name = 'report-readiness'; command = 'composer report:readiness'; skip = (-not (Test-CommandPathAvailable -Path 'tools\report\VendorReadinessReport.php')); reason = 'tools\\report\\VendorReadinessReport.php is missing' }
    )
}

if ($IncludeSmokes -and $IncludeReports) {
    $steps += @(
        @{ name = 'composer-audit'; command = 'composer audit --locked'; skip = $false; reason = '' },
        @{ name = 'importmap-audit'; command = 'php bin/console importmap:audit --no-interaction'; skip = (-not (Test-ComposerPackageInstalled -PackageName 'symfony/asset-mapper')); reason = 'symfony/asset-mapper is not installed in composer.lock' },
        @{ name = 'gitleaks'; command = 'gitleaks git --source . --config .gitleaks.toml --redact --no-banner'; skip = (-not (Test-ExecutableAvailable -Name 'gitleaks')); reason = 'gitleaks is not available in PATH' },
        @{ name = 'semgrep-ce'; command = 'semgrep scan --config auto --error'; skip = (-not (Test-ExecutableAvailable -Name 'semgrep')); reason = 'semgrep is not available in PATH' },
        @{ name = 'symfony-security-tests'; command = 'composer test:symfony-security'; skip = (-not (Test-PathPatternAvailable -Pattern '*Security*Test.php')); reason = 'no Symfony security tests found by *Security*Test.php pattern' }
    )
}

$results = @()

foreach ($step in $steps) {
    $safeName = $step.name -replace '[^a-zA-Z0-9\-_]+', '-'
    $logPath = Join-Path $logsRoot ($safeName + '.log')
    $results += Invoke-Step -Name $step.name -Command $step.command -LogPath $logPath -Skip ([bool]$step.skip) -SkipReason $step.reason
}

$failedCount = @($results | Where-Object { $_.status -eq 'failed' }).Count
$passedCount = @($results | Where-Object { $_.status -eq 'passed' }).Count
$skippedCount = @($results | Where-Object { $_.status -eq 'skipped' }).Count
$totalCount = @($results).Count

$summary = [PSCustomObject]@{
    component = 'Vendoring'
    timestamp = $timestamp
    report_root = $reportRoot
    passed = $passedCount
    non_passed = $failedCount
    skipped = $skippedCount
    total = $totalCount
    strict = [bool]$FailOnErrors
    include_smokes = [bool]$IncludeSmokes
    include_reports = [bool]$IncludeReports
    steps = $results
}

$summaryJsonPath = Join-Path $reportRoot 'summary.json'
$summaryTxtPath = Join-Path $reportRoot 'summary.txt'
$summaryMdPath = Join-Path $reportRoot 'summary.md'

$summary | ConvertTo-Json -Depth 8 | Set-Content -LiteralPath $summaryJsonPath -Encoding UTF8

$txt = @()
$txt += 'Vendoring local pipeline'
$txt += "Timestamp: $timestamp"
$txt += "Report root: $reportRoot"
$txt += "Passed: $passedCount"
$txt += "Non-passed: $failedCount"
$txt += "Skipped: $skippedCount"
$txt += "Total: $totalCount"
$txt += ''

foreach ($row in $results) {
    $txt += ('[{0}] {1} (exit={2}, duration={3}s)' -f $row.status.ToUpperInvariant(), $row.name, $row.exit_code, $row.duration_seconds)
    $txt += ('  log: {0}' -f $row.log)
}

$txt -join [Environment]::NewLine | Set-Content -LiteralPath $summaryTxtPath -Encoding UTF8

$md = @()
$md += '# Vendoring local pipeline'
$md += ''
$md += "- Timestamp: ``$timestamp``"
$md += "- Report root: ``$reportRoot``"
$md += "- Passed: **$passedCount**"
$md += "- Non-passed: **$failedCount**"
$md += "- Skipped: **$skippedCount**"
$md += "- Total: **$totalCount**"
$md += ''
$md += '| Status | Step | Exit | Duration (s) | Log |'
$md += '|---|---|---:|---:|---|'

foreach ($row in $results) {
    $logName = Split-Path -Leaf $row.log
    $md += ('| {0} | {1} | {2} | {3} | ``{4}`` |' -f $row.status, $row.name, $row.exit_code, $row.duration_seconds, $logName)
}

$md -join [Environment]::NewLine | Set-Content -LiteralPath $summaryMdPath -Encoding UTF8

Write-Host ''
Write-Host 'Pipeline summary:'
Write-Host "  report: $reportRoot"
Write-Host "  passed: $passedCount / $totalCount"
Write-Host "  non-passed: $failedCount"
Write-Host "  skipped: $skippedCount"

if ($FailOnErrors -and $failedCount -gt 0) {
    exit 1
}

exit 0
