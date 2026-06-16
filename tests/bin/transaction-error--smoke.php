<?php

declare(strict_types=1);

function vendoring_load_file_or_empty(string $path): string
{
    $contents = file_get_contents($path);

    return false === $contents ? '' : $contents;
}

$transactionHttpService = vendoring_load_file_or_empty(__DIR__.'/../../src/Service/Http/Vendor/Transaction/VendorTransactionHttpService.php');
$manager = vendoring_load_file_or_empty(__DIR__.'/../../src/Service/Transaction/VendorTransactionLifecycleService.php');
$amountPolicy = vendoring_load_file_or_empty(__DIR__.'/../../src/Service/Policy/VendorTransactionAmountPolicyService.php');
$errorCodes = vendoring_load_file_or_empty(__DIR__.'/../../src/ValueObject/VendorTransactionErrorCodeValueObject.php');

$checks = [
    'transaction HTTP service avoids raw exception message payload' => !str_contains($transactionHttpService, "['error' => \$exception->getMessage()]"),
    'transaction HTTP service normalizes validation messages through resolver' => str_contains($transactionHttpService, 'normalizeErrorCode'),
    'manager uses stable invalid transition code' => str_contains($manager, 'VendorTransactionErrorCodeValueObject::INVALID_STATUS_TRANSITION'),
    'amount policy uses stable codes' => str_contains($amountPolicy, 'VendorTransactionErrorCodeValueObject::AMOUNT_NOT_NUMERIC')
        && str_contains($amountPolicy, 'VendorTransactionErrorCodeValueObject::AMOUNT_NOT_POSITIVE'),
    'error code catalog exists' => str_contains($errorCodes, "public const string DUPLICATE_TRANSACTION = 'duplicate_transaction';")
        && str_contains($errorCodes, "public const string STATUS_REQUIRED = 'status_required';"),
];

$failed = [];
foreach ($checks as $label => $ok) {
    if (!$ok) {
        $failed[] = $label;
    }
}

if ([] !== $failed) {
    fwrite(STDERR, 'Transaction error surface smoke failed:'.PHP_EOL.' - '.implode(PHP_EOL.' - ', $failed).PHP_EOL);
    exit(1);
}

echo 'Transaction error surface smoke OK'.PHP_EOL;
