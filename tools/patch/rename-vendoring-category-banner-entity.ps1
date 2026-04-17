# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
# Rename Vendoring-local CategoryBanner entity to a component-scoped class.
# Run from the Vendoring repository root.

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$root = (Get-Location).Path
$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupRoot = Join-Path $root "var/patch-backup/vendoring-category-banner-entity-rename-$timestamp"

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

$oldFile = Join-Path $root 'src/Entity/CategoryBanner.php'
$newFile = Join-Path $root 'src/Entity/VendorCatalogCategoryBanner.php'

if (Test-Path $oldFile) {
    Backup-File $oldFile

    New-Item -ItemType Directory -Force -Path (Split-Path $newFile -Parent) | Out-Null
    $content = Get-Content $oldFile -Raw

    $content = $content -replace '\bclass\s+CategoryBanner\b', 'class VendorCatalogCategoryBanner'

    # Scope explicit generic Doctrine table names when present.
    $content = $content -replace "name:\s*'category_banner'", "name: 'vendor_catalog_category_banner'"
    $content = $content -replace 'name:\s*"category_banner"', 'name: "vendor_catalog_category_banner"'
    $content = $content -replace "name:\s*'category_banners'", "name: 'vendor_catalog_category_banners'"
    $content = $content -replace 'name:\s*"category_banners"', 'name: "vendor_catalog_category_banners"'

    Set-Content -Path $newFile -Value $content -NoNewline
    Remove-Item $oldFile -Force
}

$src = Join-Path $root 'src'
if (Test-Path $src) {
    $phpFiles = Get-ChildItem -Path $src -Recurse -Filter '*.php' -File

    foreach ($file in $phpFiles) {
        $path = $file.FullName

        Replace-In-File $path 'App\\Entity\\CategoryBanner' 'App\Entity\VendorCatalogCategoryBanner'
        Replace-In-File $path 'use\s+App\\Entity\\CategoryBanner;' 'use App\Entity\VendorCatalogCategoryBanner;'
        Replace-In-File $path '\bCategoryBanner::class\b' 'VendorCatalogCategoryBanner::class'
        Replace-In-File $path '\bnew\s+CategoryBanner\s*\(' 'new VendorCatalogCategoryBanner('
        Replace-In-File $path '\bCategoryBanner\s+\$categoryBanner\b' 'VendorCatalogCategoryBanner $categoryBanner'
        Replace-In-File $path '\?CategoryBanner\s+\$categoryBanner\b' '?VendorCatalogCategoryBanner $categoryBanner'
        Replace-In-File $path '\biterable<CategoryBanner>' 'iterable<VendorCatalogCategoryBanner>'
        Replace-In-File $path '\barray<CategoryBanner>' 'array<VendorCatalogCategoryBanner>'
    }
}

if (Test-Path $newFile) {
    php -l $newFile
}

Write-Host 'Vendoring CategoryBanner entity rename completed.'
Write-Host 'Old: src/Entity/CategoryBanner.php'
Write-Host 'New: src/Entity/VendorCatalogCategoryBanner.php'
Write-Host "Backup: $backupRoot"
