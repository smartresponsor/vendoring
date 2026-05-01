# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
# Rename Vendoring-local CategoryChangeRequest entity to a component-scoped class.
# Run from the Vendoring repository root.

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$root = (Get-Location).Path
$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupRoot = Join-Path $root "var/patch-backup/vendoring-category-change-request-entity-rename-$timestamp"

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

$oldFile = Join-Path $root 'src/Entity/CategoryChangeRequest.php'
$newFile = Join-Path $root 'src/Entity/VendorCatalogCategoryChangeRequestEntity.php'

if (Test-Path $oldFile) {
    Backup-File $oldFile

    New-Item -ItemType Directory -Force -Path (Split-Path $newFile -Parent) | Out-Null
    $content = Get-Content $oldFile -Raw

    $content = $content -replace '\bclass\s+CategoryChangeRequest\b', 'class VendorCatalogCategoryChangeRequestEntity'

    # Scope explicit generic Doctrine table names when present.
    $content = $content -replace "name:\s*'category_change_request'", "name: 'vendor_catalog_category_change_request'"
    $content = $content -replace 'name:\s*"category_change_request"', 'name: "vendor_catalog_category_change_request"'
    $content = $content -replace "name:\s*'category_change_requests'", "name: 'vendor_catalog_category_change_requests'"
    $content = $content -replace 'name:\s*"category_change_requests"', 'name: "vendor_catalog_category_change_requests"'

    Set-Content -Path $newFile -Value $content -NoNewline
    Remove-Item $oldFile -Force
}

$src = Join-Path $root 'src'
if (Test-Path $src) {
    $phpFiles = Get-ChildItem -Path $src -Recurse -Filter '*.php' -File

    foreach ($file in $phpFiles) {
        $path = $file.FullName

        Replace-In-File $path 'App\Vendoring\\Entity\\CategoryChangeRequest' 'App\Vendoring\Entity\Vendor\VendorCatalogCategoryChangeRequestEntity'
        Replace-In-File $path 'use\s+App\Vendoring\\Entity\\CategoryChangeRequest;' 'use App\Vendoring\Entity\Vendor\VendorCatalogCategoryChangeRequestEntity;'
        Replace-In-File $path '\bCategoryChangeRequest::class\b' 'VendorCatalogCategoryChangeRequestEntity::class'
        Replace-In-File $path '\bnew\s+CategoryChangeRequest\s*\(' 'new VendorCatalogCategoryChangeRequestEntity('
        Replace-In-File $path '\bCategoryChangeRequest\s+\$categoryChangeRequest\b' 'VendorCatalogCategoryChangeRequestEntity $categoryChangeRequest'
        Replace-In-File $path '\?CategoryChangeRequest\s+\$categoryChangeRequest\b' '?VendorCatalogCategoryChangeRequestEntity $categoryChangeRequest'
        Replace-In-File $path '\biterable<CategoryChangeRequest>' 'iterable<VendorCatalogCategoryChangeRequestEntity>'
        Replace-In-File $path '\barray<CategoryChangeRequest>' 'array<VendorCatalogCategoryChangeRequestEntity>'
    }
}

if (Test-Path $newFile) {
    php -l $newFile
}

Write-Host 'Vendoring CategoryChangeRequest entity rename completed.'
Write-Host 'Old: src/Entity/CategoryChangeRequest.php'
Write-Host 'New: src/Entity/VendorCatalogCategoryChangeRequestEntity.php'
Write-Host "Backup: $backupRoot"
