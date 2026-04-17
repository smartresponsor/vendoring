# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
# Rename Vendoring-local CategoryPin entity to a component-scoped class.
# Run from the Vendoring repository root.

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$root = (Get-Location).Path
$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupRoot = Join-Path $root "var/patch-backup/vendoring-category-pin-entity-rename-$timestamp"

function Backup-File([string] $path) {
    if (-not (Test-Path $path)) {
        return
    }

    $resolved = Resolve-Path $path
    $relative = $resolved.Path.Substring($root.Length).TrimStart('\', '/')
    $target = Join-Path $backupRoot $relative

    New-Item -ItemType Directory -Force -Path (Split-Path $target -Parent) | Out-Null
    Copy-Item -Path $path -Destination $target -Force
}

function Replace-In-File([string] $path, [string] $pattern, [string] $replacement) {
    if (-not (Test-Path $path)) {
        return
    }

    $content = Get-Content $path -Raw
    $updated = $content -replace $pattern, $replacement

    if ($updated -ne $content) {
        Backup-File $path
        Set-Content -Path $path -Value $updated -NoNewline
    }
}

$oldFile = Join-Path $root 'src/Entity/CategoryPin.php'
$newFile = Join-Path $root 'src/Entity/VendorCatalogCategoryPin.php'

if (Test-Path $oldFile) {
    Backup-File $oldFile

    New-Item -ItemType Directory -Force -Path (Split-Path $newFile -Parent) | Out-Null
    $content = Get-Content $oldFile -Raw

    $content = $content -replace '\bclass\s+CategoryPin\b', 'class VendorCatalogCategoryPin'

    # Scope explicit generic Doctrine table names when present.
    $content = $content -replace "name:\s*'category_pin'", "name: 'vendor_catalog_category_pin'"
    $content = $content -replace 'name:\s*"category_pin"', 'name: "vendor_catalog_category_pin"'
    $content = $content -replace "name:\s*'category_pins'", "name: 'vendor_catalog_category_pins'"
    $content = $content -replace 'name:\s*"category_pins"', 'name: "vendor_catalog_category_pins"'

    Set-Content -Path $newFile -Value $content -NoNewline
    Remove-Item $oldFile -Force
}

$src = Join-Path $root 'src'
if (Test-Path $src) {
    $phpFiles = Get-ChildItem -Path $src -Recurse -Filter '*.php' -File

    foreach ($file in $phpFiles) {
        $path = $file.FullName

        Replace-In-File $path 'App\\Entity\\CategoryPin' 'App\Entity\VendorCatalogCategoryPin'
        Replace-In-File $path 'use\s+App\\Entity\\CategoryPin;' 'use App\Entity\VendorCatalogCategoryPin;'
        Replace-In-File $path '\bCategoryPin::class\b' 'VendorCatalogCategoryPin::class'
        Replace-In-File $path '\bnew\s+CategoryPin\s*\(' 'new VendorCatalogCategoryPin('
        Replace-In-File $path '\bCategoryPin\s+\$categoryPin\b' 'VendorCatalogCategoryPin $categoryPin'
        Replace-In-File $path '\?CategoryPin\s+\$categoryPin\b' '?VendorCatalogCategoryPin $categoryPin'
        Replace-In-File $path '\biterable<CategoryPin>' 'iterable<VendorCatalogCategoryPin>'
        Replace-In-File $path '\barray<CategoryPin>' 'array<VendorCatalogCategoryPin>'
    }
}

if (Test-Path $newFile) {
    php -l $newFile
}

Write-Host 'Vendoring CategoryPin entity rename completed.'
Write-Host 'Old: src/Entity/CategoryPin.php'
Write-Host 'New: src/Entity/VendorCatalogCategoryPin.php'
Write-Host "Backup: $backupRoot"
