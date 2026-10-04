<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$services = (string) file_get_contents($root.'/config/component/services.yaml');
$servicesVendorTransactions = (string) file_get_contents($root.'/config/vendor_services_transactions.yaml');
$config = $services."\n".$servicesVendorTransactions;

$required = [
    'App\Vendoring\\RepositoryInterface\\VendorAnalyticsRepositoryInterface',
    'App\Vendoring\\RepositoryInterface\\VendorAttachmentRepositoryInterface',
    'App\Vendoring\\RepositoryInterface\\VendorDocumentRepositoryInterface',
    'App\Vendoring\\RepositoryInterface\\VendorLedgerBindingRepositoryInterface',
    'App\Vendoring\\RepositoryInterface\\VendorSecurityRepositoryInterface',
    'App\Vendoring\\ServiceInterface\\Integration\\VendorCrmServiceInterface',
    'App\Vendoring\\ServiceInterface\\Billing\\VendorBillingServiceInterface',
    'App\Vendoring\\ServiceInterface\\Document\\VendorDocumentServiceInterface',
    'App\Vendoring\\ServiceInterface\\Media\\VendorMediaServiceInterface',
    'App\Vendoring\\ServiceInterface\\Identity\\VendorPassportServiceInterface',
    'App\Vendoring\\ServiceInterface\\Profile\\VendorProfileServiceInterface',
    'App\Vendoring\\ServiceInterface\\Crud\\VendorCrudServiceInterface',
    'App\Vendoring\\ServiceInterface\\Ledger\\VendorDoubleEntryServiceInterface',
    'App\Vendoring\\ProviderInterface\\Payout\\VendorPayoutProviderInterface',
    'App\Vendoring\\ServiceInterface\\Payout\\VendorSettlementCalculatorServiceInterface',
];

foreach ($required as $interfaceClass) {
    if (!str_contains($config, $interfaceClass.':')) {
        fwrite(STDERR, 'Missing interface alias: '.$interfaceClass.PHP_EOL);
        exit(1);
    }
}

echo "Interface alias smoke OK\n";
