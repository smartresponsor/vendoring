<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2);
$amountPolicy = (string) file_get_contents($root . '/src/Service/Policy/VendorTransactionAmountPolicy.php');
$statusPolicy = (string) file_get_contents($root . '/src/Service/Policy/VendorTransactionStatusPolicy.php');
$servicesYaml = (string) file_get_contents($root . '/config/vendor_services.yaml');
$composer = vendoring_load_composer_json($root);

if (!vendoring_has_script($composer, 'test:transaction-policy')) {
    fwrite(STDERR, "composer.json must define test:transaction-policy\n");
    exit(1);
}
if (!str_contains($amountPolicy, 'VendorTransactionErrorCode::AMOUNT_NOT_NUMERIC') || !str_contains($amountPolicy, 'VendorTransactionErrorCode::AMOUNT_NOT_POSITIVE')) {
    fwrite(STDERR, "Amount policy must use stable error codes.\n");
    exit(1);
}
if (!str_contains($statusPolicy, 'VendorTransactionErrorCode::STATUS_REQUIRED') || !str_contains($statusPolicy, 'VendorTransactionErrorCode::INVALID_STATUS_TRANSITION')) {
    fwrite(STDERR, "Status policy must use stable status error codes.\n");
    exit(1);
}
if (!str_contains($servicesYaml, "App\Vendoring\\ServiceInterface\\Policy\\VendorTransactionAmountPolicyInterface: '@App\Vendoring\\Service\\Policy\\VendorTransactionAmountPolicy'")) {
    fwrite(STDERR, "vendor_services.yaml must alias VendorTransactionAmountPolicyInterface.\n");
    exit(1);
}
if (!str_contains($servicesYaml, "App\Vendoring\\ServiceInterface\\Policy\\VendorTransactionStatusPolicyInterface: '@App\Vendoring\\Service\\Policy\\VendorTransactionStatusPolicy'")) {
    fwrite(STDERR, "vendor_services.yaml must alias VendorTransactionStatusPolicyInterface.\n");
    exit(1);
}

echo "transaction policy smoke passed\n";
