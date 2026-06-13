<?php

declare(strict_types=1);

require_once __DIR__ . '/_composer_json.php';

$root = dirname(__DIR__, 2);
$policyCode = (string) file_get_contents($root . '/src/Service/Policy/VendorTransactionAmountPolicyService.php');
$composer = vendoring_load_composer_json($root);

if (!str_contains($policyCode, 'amount_not_numeric') || !str_contains($policyCode, 'amount_not_positive')) {
    fwrite(STDERR, "Amount policy must use stable amount error codes\n");
    exit(1);
}

if (!vendoring_has_script($composer, 'test:transaction-amount')) {
    fwrite(STDERR, "composer.json must define test:transaction-amount\n");
    exit(1);
}

echo "transaction amount smoke passed\n";
