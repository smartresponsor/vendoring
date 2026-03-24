<?php

declare(strict_types=1);

use App\Form\Ops\VendorTransactionCreateType;
use App\Form\Ops\VendorTransactionStatusUpdateType;
use Symfony\Component\Config\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    if (!class_exists('Symfony\\Component\\Form\\AbstractType')) {
        return;
    }

    $services = $configurator->services();
    $services->set(VendorTransactionCreateType::class)
        ->autowire()
        ->autoconfigure();
    $services->set(VendorTransactionStatusUpdateType::class)
        ->autowire()
        ->autoconfigure();
};
