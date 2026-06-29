<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

class_alias(
    App\Vendoring\Service\Vendor\Transaction\Operator\VendorTransactionOperatorService::class,
    'App\\Vendoring\\Tests\\Unit\\Ops\\App\\Vendoring\\Service\\Vendor\\Transaction\\Operator\\VendorTransactionOperatorService',
);
