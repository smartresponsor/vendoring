param(
  [Parameter(Mandatory=$false)]
  [string]$RepoRoot = "."
)

$RepoRoot = $RepoRoot.TrimEnd('\','/')

$requiredFiles = @(".gitignore","MANIFEST.json","README.md")
$allowedFiles = @(".gitignore","MANIFEST.json","README.md",".gitattributes",".php-cs-fixer.php",".php-cs-fixer.dist.php","phpstan.neon","phpunit.xml","phpunit.xml.dist","phpunit.xsd","composer.json","composer.lock","symfony.lock")
$allowedNonDotDir = @(
  "src","config","docs","migrations","public","bin","templates","tests","var","assets",".smartresponsor"
)

$forbiddenRootDir = @(
  "Vendor","tools"
)

function IsAllowedRootFile([string]$name) {
  return $allowedFiles -contains $name
}

function IsAllowedRootDir([string]$name) {
  if ($name -eq ".git") { return $true }
  foreach ($f in $forbiddenRootDir) { if ($name -ieq $f) { return $false } }
  if ($name.StartsWith(".")) { return $true }
  return $allowedNonDotDir -contains $name
}


$issues = New-Object System.Collections.Generic.List[string]
$fail = $false

# Enumerate root entries
Get-ChildItem -LiteralPath $RepoRoot -Force | ForEach-Object {
  $name = $_.Name

  if ($name -eq ".git") {
    return
  }

  if ($_.PSIsContainer) {
    if (-not (IsAllowedRootDir $name)) {
      $issues.Add(" - Non-dot folder in root: $name")
      $fail = $true
    }
  } else {
    if (-not (IsAllowedRootFile $name)) {
      $issues.Add(" - Unexpected root file: $name")
      $fail = $true
    }
  }
}

# Required files
foreach ($req in $requiredFiles) {
  $p = Join-Path $RepoRoot $req
  if (-not (Test-Path -LiteralPath $p -PathType Leaf)) {
    $issues.Add(" - Missing required root file: $req")
    $fail = $true
  }
}

if ($fail) {
  Write-Host ""
  Write-Host "Root contract FAILED:"
  Write-Host ""
  foreach ($i in $issues) { Write-Host $i }

  $reportDir = Join-Path $RepoRoot ".report"
  New-Item -ItemType Directory -Force -Path $reportDir | Out-Null
  New-Item -ItemType File -Force -Path (Join-Path $reportDir "gate-flag-root-contract.fail") | Out-Null

  exit 2
}

Write-Host "Root contract OK"
exit 0
