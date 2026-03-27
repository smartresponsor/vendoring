<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$services = (string) file_get_contents($root.'/config/vendor_services.yaml');
$servicesVendorTransactions = (string) file_get_contents($root.'/config/vendor_services_transactions.yaml');
$config = $services."\n".$servicesVendorTransactions;

$required = [
    'App\\RepositoryInterface\\VendorAnalyticsRepositoryInterface',
    'App\\RepositoryInterface\\VendorAttachmentRepositoryInterface',
    'App\\RepositoryInterface\\VendorDocumentRepositoryInterface',
    'App\\RepositoryInterface\\VendorLedgerBindingRepositoryInterface',
    'App\\RepositoryInterface\\VendorSecurityRepositoryInterface',
    'App\\ServiceInterface\\VendorCrmServiceInterface',
    'App\\ServiceInterface\\VendorBillingServiceInterface',
    'App\\ServiceInterface\\VendorDocumentServiceInterface',
    'App\\ServiceInterface\\VendorMediaServiceInterface',
    'App\\ServiceInterface\\VendorPassportServiceInterface',
    'App\\ServiceInterface\\VendorProfileServiceInterface',
    'App\\ServiceInterface\\VendorServiceInterface',
    'App\\ServiceInterface\\Ledger\\VendorDoubleEntryServiceInterface',
    'App\\ServiceInterface\\Payout\\VendorPayoutProviderServiceInterface',
    'App\\ServiceInterface\\Payout\\VendorSettlementCalculatorServiceInterface',
];

foreach ($required as $interfaceClass) {
    if (!str_contains($config, $interfaceClass.':')) {
        fwrite(STDERR, 'Missing interface alias: '.$interfaceClass.PHP_EOL);
        exit(1);
    }
}

echo "Interface alias smoke OK\n";
