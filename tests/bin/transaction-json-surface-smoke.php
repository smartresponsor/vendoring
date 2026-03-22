<?php

declare(strict_types=1);

$composer = json_decode((string) file_get_contents(__DIR__.'/../../composer.json'), true, flags: JSON_THROW_ON_ERROR);
$scripts = $composer['scripts'] ?? [];
if (!isset($scripts['test:transaction-json'])) {
    fwrite(STDERR, 'Missing composer script: test:transaction-json'.PHP_EOL);
    exit(1);
}

$controller = (string) file_get_contents(__DIR__.'/../../src/Controller/VendorTransactionController.php');
if (!str_contains($controller, 'VendorTransactionErrorCode::MALFORMED_JSON')) {
    fwrite(STDERR, 'Controller does not map malformed JSON'.PHP_EOL);
    exit(1);
}
if (!str_contains($controller, 'catch (JsonException)')) {
    fwrite(STDERR, 'Controller does not catch JsonException'.PHP_EOL);
    exit(1);
}

echo 'transaction json surface smoke OK'.PHP_EOL;

if (!str_contains($controller, 'public function create')) {
    fwrite(STDERR, 'Missing create() action'.PHP_EOL);
    exit(1);
}
if (!str_contains($controller, 'public function updateStatus')) {
    fwrite(STDERR, 'Missing updateStatus() action'.PHP_EOL);
    exit(1);
}
if (substr_count($controller, 'VendorTransactionErrorCode::MALFORMED_JSON') < 2) {
    fwrite(STDERR, 'Malformed JSON must be handled in both transaction actions'.PHP_EOL);
    exit(1);
}
