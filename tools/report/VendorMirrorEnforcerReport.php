<?php

declare(strict_types=1);

require __DIR__.'/_vendor_report_bootstrap.php';

vendorReportHeader('Vendor mirror enforcer report');
$root = vendorReportProjectRoot();

$checks = [
    ['src/Repository/VendorRepository.php', 'src/RepositoryInterface/VendorRepositoryInterface.php'],
    ['src/Repository/VendorApiKeyRepository.php', 'src/RepositoryInterface/VendorApiKeyRepositoryInterface.php'],
    ['src/Repository/VendorDocumentRepository.php', 'src/RepositoryInterface/VendorDocumentRepositoryInterface.php'],
    ['src/Repository/VendorMediaRepository.php', 'src/RepositoryInterface/VendorMediaRepositoryInterface.php'],
    ['src/Repository/VendorPassportRepository.php', 'src/RepositoryInterface/VendorPassportRepositoryInterface.php'],
    ['src/Repository/VendorProfileRepository.php', 'src/RepositoryInterface/VendorProfileRepositoryInterface.php'],
    ['src/Repository/VendorSecurityRepository.php', 'src/RepositoryInterface/VendorSecurityRepositoryInterface.php'],
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
    $ok = is_file($root.'/'.$implementation) && is_file($root.'/'.$contract);
    vendorReportPrintCheck($implementation.' <-> '.$contract, $ok);
    if (!$ok) {
        $hasWarning = true;
    }
}

exit($hasWarning ? 1 : 0);
