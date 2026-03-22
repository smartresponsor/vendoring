<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$manager = (string) file_get_contents($root.'/src/Service/VendorTransactionManager.php');
$controller = (string) file_get_contents($root.'/src/Controller/VendorTransactionController.php');
$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);

$checks = [
    'manager normalizes vendorId' => str_contains($manager, 'normalizeRequiredIdentity($data->vendorId, VendorTransactionErrorCode::VENDOR_ID_REQUIRED)'),
    'manager normalizes orderId' => str_contains($manager, 'normalizeRequiredIdentity($data->orderId, VendorTransactionErrorCode::ORDER_ID_REQUIRED)'),
    'manager has identity normalizer' => str_contains($manager, 'private function normalizeRequiredIdentity(string $value, string $message): string'),
    'controller keeps trimmed vendorId' => str_contains($controller, "vendorId: trim((string) \$payload['vendorId'])"),
    'controller keeps trimmed orderId' => str_contains($controller, "orderId: trim((string) \$payload['orderId'])"),
    'composer script test:transaction-identity exists' => isset($composer['scripts']['test:transaction-identity']),
];

foreach ($checks as $label => $ok) {
    if (true !== $ok) {
        fwrite(STDERR, '[FAIL] '.$label.PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] '.$label.PHP_EOL);
}
