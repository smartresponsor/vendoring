<?php

declare(strict_types=1);

require __DIR__ . '/_vendor_report_bootstrap.php';

vendorReportHeader('VendorEntity mirror enforcer report');
$root = vendorReportProjectRoot();

$checks = [
    ['src/Repository/Vendor/VendorRepository.php', 'src/RepositoryInterface/Vendor/VendorRepositoryInterface.php'],
    ['src/Repository/Vendor/VendorApiKeyRepository.php', 'src/RepositoryInterface/Vendor/VendorApiKeyRepositoryInterface.php'],
    ['src/Repository/Vendor/VendorDocumentRepository.php', 'src/RepositoryInterface/Vendor/VendorDocumentRepositoryInterface.php'],
    ['src/Repository/Vendor/VendorMediaRepository.php', 'src/RepositoryInterface/Vendor/VendorMediaRepositoryInterface.php'],
    ['src/Repository/Vendor/VendorPassportRepository.php', 'src/RepositoryInterface/Vendor/VendorPassportRepositoryInterface.php'],
    ['src/Repository/Vendor/VendorProfileRepository.php', 'src/RepositoryInterface/Vendor/VendorProfileRepositoryInterface.php'],
    ['src/Repository/Vendor/VendorSecurityRepository.php', 'src/RepositoryInterface/Vendor/VendorSecurityRepositoryInterface.php'],
    ['src/Service/VendorService.php', 'src/ServiceInterface/VendorServiceInterface.php'],
    ['src/Service/VendorDocumentService.php', 'src/ServiceInterface/VendorDocumentServiceInterface.php'],
    ['src/Service/VendorMediaService.php', 'src/ServiceInterface/VendorMediaServiceInterface.php'],
    ['src/Service/VendorPassportService.php', 'src/ServiceInterface/VendorPassportServiceInterface.php'],
    ['src/Service/VendorProfileService.php', 'src/ServiceInterface/VendorProfileServiceInterface.php'],
    ['src/Service/VendorBillingService.php', 'src/ServiceInterface/VendorBillingServiceInterface.php'],
    ['src/Service/VendorSecurityService.php', 'src/ServiceInterface/VendorSecurityServiceInterface.php'],
    ['src/Service/VendorApiKeyService.php', 'src/ServiceInterface/VendorApiKeyServiceInterface.php'],
];

$hasWarning = false;
foreach ($checks as [$implementation, $contract]) {
    $ok = is_file($root . '/' . $implementation) && is_file($root . '/' . $contract);
    vendorReportPrintCheck($implementation . ' <-> ' . $contract, $ok);
    if (!$ok) {
        $hasWarning = true;
    }
}

exit($hasWarning ? 1 : 0);
