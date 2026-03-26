<?php

declare(strict_types=1);

require_once __DIR__.'/_composer_json.php';

$composer = vendoring_load_composer_json(dirname(__DIR__, 2));
$scripts = vendoring_composer_section($composer, 'scripts');
if (!isset($scripts['test:transaction-json'])) {
    fwrite(STDERR, 'Missing composer script: test:transaction-json'.PHP_EOL);
    exit(1);
}

$controller = (string) file_get_contents(__DIR__.'/../../src/Controller/VendorTransactionController.php');
if (!str_contains($controller, 'VendorTransactionErrorCode::MALFORMED_JSON')) {
    fwrite(STDERR, 'Controller does not map malformed JSON'.PHP_EOL);
    exit(1);
}
if (!str_contains($controller, 'catch (JsonException $exception)')) {
    fwrite(STDERR, 'Controller does not catch JsonException with exception context'.PHP_EOL);
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
