<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$required = [
    $root.'/src/Repository/VendorRepository.php',
    $root.'/src/Repository/VendorApiKeyRepository.php',
    $root.'/src/Repository/VendorTransactionRepository.php',
    $root.'/src/Repository/Payout/PayoutRepository.php',
    $root.'/src/Repository/Ledger/LedgerEntryRepository.php',
    $root.'/tests/Unit/Repository/DoctrineRepositoryContractTest.php',
];

foreach ($required as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, 'Missing repository contract file: '.$path.PHP_EOL);
        exit(1);
    }
}

echo "repository-contract smoke passed\n";
