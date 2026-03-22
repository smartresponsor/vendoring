<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$manager = (string) file_get_contents($root.'/src/Service/VendorTransactionManager.php');
$controller = (string) file_get_contents($root.'/src/Controller/VendorTransactionController.php');
$services = (string) file_get_contents($root.'/config/services.yaml');

if (!file_exists($root.'/src/Service/Policy/VendorTransactionStatusPolicy.php')) {
    fwrite(STDERR, "Missing VendorTransactionStatusPolicy service.\n");
    exit(1);
}

if (!file_exists($root.'/src/ServiceInterface/Policy/VendorTransactionStatusPolicyInterface.php')) {
    fwrite(STDERR, "Missing VendorTransactionStatusPolicyInterface contract.\n");
    exit(1);
}

if (!isset($composer['scripts']['test:transaction-policy'])) {
    fwrite(STDERR, "Missing composer script test:transaction-policy.\n");
    exit(1);
}

if (!str_contains($manager, 'VendorTransactionStatusPolicyInterface')) {
    fwrite(STDERR, "VendorTransactionManager must depend on VendorTransactionStatusPolicyInterface.\n");
    exit(1);
}

if (!str_contains($manager, 'VendorTransactionErrorCode::INVALID_STATUS_TRANSITION')) {
    fwrite(STDERR, "VendorTransactionManager must guard invalid status transitions.\n");
    exit(1);
}

if (!str_contains($controller, 'VendorTransactionErrorCode::STATUS_REQUIRED')) {
    fwrite(STDERR, "VendorTransactionController must validate explicit status payload.\n");
    exit(1);
}

if (!str_contains($controller, 'InvalidArgumentException')) {
    fwrite(STDERR, "VendorTransactionController must map invalid transition exception to 422.\n");
    exit(1);
}

if (!str_contains($services, "App\\ServiceInterface\\Policy\\VendorTransactionStatusPolicyInterface: '@App\\Service\\Policy\\VendorTransactionStatusPolicy'")) {
    fwrite(STDERR, "services.yaml must alias VendorTransactionStatusPolicyInterface.\n");
    exit(1);
}

fwrite(STDOUT, "transaction policy smoke passed\n");
