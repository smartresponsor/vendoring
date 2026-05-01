<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$services = (string) file_get_contents($root . '/config/component/services.yaml');
$servicesVendorTransactions = (string) file_get_contents($root . '/config/vendor_services_transactions.yaml');
$config = $services . "\n" . $servicesVendorTransactions;

$required = [
    'App\Vendoring\\RepositoryInterface\\VendorAnalyticsRepositoryInterface',
    'App\Vendoring\\RepositoryInterface\\VendorAttachmentRepositoryInterface',
    'App\Vendoring\\RepositoryInterface\\VendorDocumentRepositoryInterface',
    'App\Vendoring\\RepositoryInterface\\VendorLedgerBindingRepositoryInterface',
    'App\Vendoring\\RepositoryInterface\\VendorSecurityRepositoryInterface',
    'App\Vendoring\\ServiceInterface\\VendorCrmServiceInterface',
    'App\Vendoring\\ServiceInterface\\VendorBillingServiceInterface',
    'App\Vendoring\\ServiceInterface\\VendorDocumentServiceInterface',
    'App\Vendoring\\ServiceInterface\\VendorMediaServiceInterface',
    'App\Vendoring\\ServiceInterface\\VendorPassportServiceInterface',
    'App\Vendoring\\ServiceInterface\\VendorProfileServiceInterface',
    'App\Vendoring\\ServiceInterface\\VendorServiceInterface',
    'App\Vendoring\\ServiceInterface\\Ledger\\VendorDoubleEntryServiceInterface',
    'App\Vendoring\\ServiceInterface\\VendorPayoutEntity\\VendorPayoutProviderServiceInterface',
    'App\Vendoring\\ServiceInterface\\VendorPayoutEntity\\VendorSettlementCalculatorServiceInterface',
];

foreach ($required as $interfaceClass) {
    if (!str_contains($config, $interfaceClass . ':')) {
        fwrite(STDERR, 'Missing interface alias: ' . $interfaceClass . PHP_EOL);
        exit(1);
    }
}

echo "Interface alias smoke OK\n";
