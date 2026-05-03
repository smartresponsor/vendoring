<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$services = (string) file_get_contents($root . '/config/component/services.yaml');
$servicesVendorTransactions = (string) file_get_contents($root . '/config/vendor_services_transactions.yaml');
$config = $services . "\n" . $servicesVendorTransactions;

$required = [
    'App\Vendoring\\RepositoryInterface\\Vendor\\VendorAnalyticsRepositoryInterface',
    'App\Vendoring\\RepositoryInterface\\Vendor\\VendorAttachmentRepositoryInterface',
    'App\Vendoring\\RepositoryInterface\\Vendor\\VendorDocumentRepositoryInterface',
    'App\Vendoring\\RepositoryInterface\\Vendor\\VendorLedgerBindingRepositoryInterface',
    'App\Vendoring\\RepositoryInterface\\Vendor\\VendorSecurityRepositoryInterface',
    'App\Vendoring\\ServiceInterface\\Integration\\VendorCrmServiceInterface',
    'App\Vendoring\\ServiceInterface\\Billing\\VendorBillingServiceInterface',
    'App\Vendoring\\ServiceInterface\\Document\\VendorDocumentServiceInterface',
    'App\Vendoring\\ServiceInterface\\Media\\VendorMediaServiceInterface',
    'App\Vendoring\\ServiceInterface\\Identity\\VendorPassportServiceInterface',
    'App\Vendoring\\ServiceInterface\\Profile\\VendorProfileServiceInterface',
    'App\Vendoring\\ServiceInterface\\Core\\VendorCoreServiceInterface',
    'App\Vendoring\\ServiceInterface\\Ledger\\VendorDoubleEntryServiceInterface',
    'App\Vendoring\\ServiceInterface\\Payout\\VendorPayoutProviderServiceInterface',
    'App\Vendoring\\ServiceInterface\\Payout\\VendorSettlementCalculatorServiceInterface',
];

foreach ($required as $interfaceClass) {
    if (!str_contains($config, $interfaceClass . ':')) {
        fwrite(STDERR, 'Missing interface alias: ' . $interfaceClass . PHP_EOL);
        exit(1);
    }
}

echo "Interface alias smoke OK\n";
