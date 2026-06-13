<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2);
$amountPolicy = (string) file_get_contents($root . '/src/Service/Policy/VendorTransactionAmountPolicyService.php');
$statusPolicy = (string) file_get_contents($root . '/src/Service/Policy/VendorTransactionStatusPolicyService.php');
$servicesYaml = (string) file_get_contents($root . '/config/component/services.yaml');
$composer = vendoring_load_composer_json($root);

if (!vendoring_has_script($composer, 'test:transaction-policy')) {
    fwrite(STDERR, "composer.json must define test:transaction-policy\n");
    exit(1);
}
if (!str_contains($amountPolicy, 'VendorTransactionErrorCodeValueObject::AMOUNT_NOT_NUMERIC') || !str_contains($amountPolicy, 'VendorTransactionErrorCodeValueObject::AMOUNT_NOT_POSITIVE')) {
    fwrite(STDERR, "Amount policy must use stable error codes.\n");
    exit(1);
}
if (!str_contains($statusPolicy, 'VendorTransactionErrorCodeValueObject::STATUS_REQUIRED') || !str_contains($statusPolicy, 'VendorTransactionErrorCodeValueObject::INVALID_STATUS_TRANSITION')) {
    fwrite(STDERR, "Status policy must use stable status error codes.\n");
    exit(1);
}
if (!str_contains($servicesYaml, "App\\Vendoring\\ServiceInterface\\Policy\\VendorTransactionAmountPolicyServiceInterface: '@App\\Vendoring\\Service\\Policy\\VendorTransactionAmountPolicyService'")) {
    fwrite(STDERR, "component/services.yaml must alias VendorTransactionAmountPolicyServiceInterface.\n");
    exit(1);
}
if (!str_contains($servicesYaml, "App\\Vendoring\\ServiceInterface\\Policy\\VendorTransactionStatusPolicyServiceInterface: '@App\\Vendoring\\Service\\Policy\\VendorTransactionStatusPolicyService'")) {
    fwrite(STDERR, "component/services.yaml must alias VendorTransactionStatusPolicyServiceInterface.\n");
    exit(1);
}

echo "transaction policy smoke passed\n";
