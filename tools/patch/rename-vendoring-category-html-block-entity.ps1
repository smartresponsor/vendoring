# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
# Rename Vendoring-local CategoryHtmlBlock entity to a component-scoped class.
# Run from the Vendoring repository root.

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$root = (Get-Location).Path
$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupRoot = Join-Path $root "var/patch-backup/vendoring-category-html-block-entity-rename-$timestamp"

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

$oldFile = Join-Path $root 'src/Entity/CategoryHtmlBlock.php'
$newFile = Join-Path $root 'src/Entity/VendorCatalogCategoryHtmlBlock.php'

if (Test-Path $oldFile) {
    Backup-File $oldFile

    New-Item -ItemType Directory -Force -Path (Split-Path $newFile -Parent) | Out-Null
    $content = Get-Content $oldFile -Raw

    $content = $content -replace '\bclass\s+CategoryHtmlBlock\b', 'class VendorCatalogCategoryHtmlBlock'

    # Scope explicit generic Doctrine table names when present.
    $content = $content -replace "name:\s*'category_html_block'", "name: 'vendor_catalog_category_html_block'"
    $content = $content -replace 'name:\s*"category_html_block"', 'name: "vendor_catalog_category_html_block"'
    $content = $content -replace "name:\s*'category_html_blocks'", "name: 'vendor_catalog_category_html_blocks'"
    $content = $content -replace 'name:\s*"category_html_blocks"', 'name: "vendor_catalog_category_html_blocks"'

    Set-Content -Path $newFile -Value $content -NoNewline
    Remove-Item $oldFile -Force
}

$src = Join-Path $root 'src'
if (Test-Path $src) {
    $phpFiles = Get-ChildItem -Path $src -Recurse -Filter '*.php' -File

    foreach ($file in $phpFiles) {
        $path = $file.FullName

        Replace-In-File $path 'App\\Entity\\CategoryHtmlBlock' 'App\Entity\VendorCatalogCategoryHtmlBlock'
        Replace-In-File $path 'use\s+App\\Entity\\CategoryHtmlBlock;' 'use App\Entity\VendorCatalogCategoryHtmlBlock;'
        Replace-In-File $path '\bCategoryHtmlBlock::class\b' 'VendorCatalogCategoryHtmlBlock::class'
        Replace-In-File $path '\bnew\s+CategoryHtmlBlock\s*\(' 'new VendorCatalogCategoryHtmlBlock('
        Replace-In-File $path '\bCategoryHtmlBlock\s+\$categoryHtmlBlock\b' 'VendorCatalogCategoryHtmlBlock $categoryHtmlBlock'
        Replace-In-File $path '\?CategoryHtmlBlock\s+\$categoryHtmlBlock\b' '?VendorCatalogCategoryHtmlBlock $categoryHtmlBlock'
        Replace-In-File $path '\biterable<CategoryHtmlBlock>' 'iterable<VendorCatalogCategoryHtmlBlock>'
        Replace-In-File $path '\barray<CategoryHtmlBlock>' 'array<VendorCatalogCategoryHtmlBlock>'
    }
}

if (Test-Path $newFile) {
    php -l $newFile
}

Write-Host 'Vendoring CategoryHtmlBlock entity rename completed.'
Write-Host 'Old: src/Entity/CategoryHtmlBlock.php'
Write-Host 'New: src/Entity/VendorCatalogCategoryHtmlBlock.php'
Write-Host "Backup: $backupRoot"
