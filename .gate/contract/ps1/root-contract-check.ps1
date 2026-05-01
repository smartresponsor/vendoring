param(
  [Parameter(Mandatory=$false)]
  [string]$RepoRoot = "."
)

while ($RepoRoot.EndsWith('\') -or $RepoRoot.EndsWith('/')) {
  $RepoRoot = $RepoRoot.Substring(0, $RepoRoot.Length - 1)
}
if ([string]::IsNullOrWhiteSpace($RepoRoot)) {
  $RepoRoot = "."
}

$requiredFiles = @(".gitignore", "README.md", "composer.json")
$allowedFiles = @(".gitignore", "README.md", "composer.json")
$allowedDotDir = @(
  ".canonization",
  ".commanding",
  ".consuming",
  ".gate",
  ".github",
  ".ide",
  ".intelligence"
)
$allowedNonDotDir = @(
  "bin",
  "build",
  "config",
  "delivery",
  "deploy",
  "docs",
  "drivers",
  "migrations",
  "ops",
  "public",
  "src"
)
$forbiddenRootDir = @(
  "Vendor",
  "tools",
  ".deploy",
  ".release",
  ".smoke",
  ".idea",
  "logs",
  "scripts"
)

function IsAllowedRootFile([string]$name) {
  return $allowedFiles -contains $name
}

function IsAllowedRootDir([string]$name) {
  if ($name -eq ".git") { return $true }
  foreach ($f in $forbiddenRootDir) { if ($name -ieq $f) { return $false } }
  if ($name.StartsWith(".")) { return $allowedDotDir -contains $name }
  return $allowedNonDotDir -contains $name
}

$issues = New-Object System.Collections.Generic.List[string]
$fail = $false

Get-ChildItem -LiteralPath $RepoRoot -Force | ForEach-Object {
  $name = $_.Name

  if ($name -eq ".git") {
    return
  }

  if ($_.PSIsContainer) {
    if (-not (IsAllowedRootDir $name)) {
      $issues.Add(" - Unexpected root directory: $name")
      $fail = $true
    }
  } else {
    if (-not (IsAllowedRootFile $name)) {
      $issues.Add(" - Unexpected root file: $name")
      $fail = $true
    }
  }
}

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

  $reportDir = Join-Path $RepoRoot "build/reports/gate"
  New-Item -ItemType Directory -Force -Path $reportDir | Out-Null
  New-Item -ItemType File -Force -Path (Join-Path $reportDir "root-contract.fail") | Out-Null

  exit 2
}

Write-Host "Root contract OK"
exit 0
