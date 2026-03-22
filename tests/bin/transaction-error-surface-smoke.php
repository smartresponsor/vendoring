<?php

declare(strict_types=1);

$controller = file_get_contents(__DIR__.'/../../src/Controller/VendorTransactionController.php');
$manager = file_get_contents(__DIR__.'/../../src/Service/VendorTransactionManager.php');
$amountPolicy = file_get_contents(__DIR__.'/../../src/Service/Policy/VendorTransactionAmountPolicy.php');
$errorCodes = file_get_contents(__DIR__.'/../../src/ValueObject/VendorTransactionErrorCode.php');

$checks = [
    'controller avoids raw exception message payload' => !str_contains($controller, "['error' => \$exception->getMessage()]"),
    'controller normalizes unknown validation messages' => str_contains($controller, 'transaction_validation_error'),
    'manager uses stable invalid transition code' => str_contains($manager, 'VendorTransactionErrorCode::INVALID_STATUS_TRANSITION'),
    'amount policy uses stable codes' => str_contains($amountPolicy, 'VendorTransactionErrorCode::AMOUNT_NOT_NUMERIC')
        && str_contains($amountPolicy, 'VendorTransactionErrorCode::AMOUNT_NOT_POSITIVE'),
    'error code catalog exists' => str_contains($errorCodes, "public const DUPLICATE_TRANSACTION = 'duplicate_transaction';")
        && str_contains($errorCodes, "public const STATUS_REQUIRED = 'status_required';"),
];

$failed = [];
foreach ($checks as $label => $ok) {
    if (!$ok) {
        $failed[] = $label;
    }
}

if ([] !== $failed) {
    fwrite(STDERR, 'Transaction error surface smoke failed:
 - '.implode('
 - ', $failed).'
');
    exit(1);
}

echo 'Transaction error surface smoke OK
';
